<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecipeControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'testuser',
            'PHP_AUTH_PW' => 'supersecurepassword!',
        ));
        $this->client->catchExceptions(false);
    }

    public function testIndexAction()
    {
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDelete()
    {
        $this->client->request('GET', '/recipes/delete/1');
        self::assertTrue($this->client->getResponse()->isRedirect('/'));
    }


}
