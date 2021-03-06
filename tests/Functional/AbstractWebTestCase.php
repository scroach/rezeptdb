<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AbstractWebTestCase extends WebTestCase
{
    /** @var Client */
    public $client;

    public function setUp(): void
    {
    	$this->loginRick();
    }

	public function loginRick() {
		$this->client = static::createClient([], [
			'PHP_AUTH_USER' => 'rick',
			'PHP_AUTH_PW' => 'supersecurepassword!',
		]);
		$this->client->catchExceptions(false);
    }

	public function loginMorty() {
		$this->client = static::createClient([], [
			'PHP_AUTH_USER' => 'morty',
			'PHP_AUTH_PW' => 'supersecurepassword!',
		]);
		$this->client->catchExceptions(false);
    }

    public function assertResponseContains(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getResponse()->getContent());
    }

    public function assertSuccessMessage(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getCrawler()->filter('.ui.positive.message')->text());
    }

    public function assertWarningMessage(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getCrawler()->filter('.ui.warning.message')->text());
    }

    public function assertErrorMessage(string $string): void
    {
        self::assertStringContainsString($string, $this->client->getCrawler()->filter('.ui.negative.message')->text());
    }


}
