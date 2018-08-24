<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractWebTestCase extends WebTestCase
{
    /** @var Client */
    public $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'testuser',
            'PHP_AUTH_PW' => 'supersecurepassword!',
        ));
        $this->client->catchExceptions(false);
    }

    public function assertResponseContains(string $string): void
    {
        self::assertContains($string, $this->client->getResponse()->getContent());
    }

    public function assertSuccessMessage(string $string): void
    {
        self::assertContains($string, $this->client->getCrawler()->filter('.ui.positive.message')->text());
    }

    public function assertErrorMessage(string $string): void
    {
        self::assertContains($string, $this->client->getCrawler()->filter('.ui.negative.message')->text());
    }


}
