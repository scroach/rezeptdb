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

    /** @var Client */
    public $clientAnon;

    public function setUp(): void
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'testuser',
            'PHP_AUTH_PW' => 'supersecurepassword!',
		]);
        $this->client->catchExceptions(false);

        $this->clientAnon = static::createClient();
        $this->clientAnon->catchExceptions(false);
    }

    public function assertResponseContains(string $string): void
    {
        self::assertContains($string, $this->client->getResponse()->getContent());
    }

    public function assertSuccessMessage(string $string): void
    {
        self::assertContains($string, $this->client->getCrawler()->filter('.ui.positive.message')->text());
    }

    public function assertWarningMessage(string $string): void
    {
        self::assertContains($string, $this->client->getCrawler()->filter('.ui.warning.message')->text());
    }

    public function assertErrorMessage(string $string): void
    {
        self::assertContains($string, $this->client->getCrawler()->filter('.ui.negative.message')->text());
    }


}
