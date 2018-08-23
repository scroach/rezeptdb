<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

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

    public function testLoadMoreRecipes()
    {
        /** @var Crawler $crawler */
        $url = '/recipes/loadMoreRecipes';
        $crawler = $this->client->xmlHttpRequest('GET', $url);
        self::assertEquals(20, $crawler->filter('.ui.card')->count());
        self::assertEquals(1, $crawler->filter('.ui.card')->first()->attr('data-recipe-id'));

        $crawler = $this->client->xmlHttpRequest('GET', $url, array('excludeIds' => range(1,10)));
        self::assertEquals(10, $crawler->filter('.ui.card')->count());
        self::assertEquals(11, $crawler->filter('.ui.card')->first()->attr('data-recipe-id'));

        $crawler = $this->client->xmlHttpRequest('GET', $url, array('excludeIds' => range(1,20)));
        self::assertEquals(0, $crawler->filter('.ui.card')->count());
        self::assertEquals('{"message":"Keine Rezepte mehr :("}', $this->client->getResponse()->getContent());
    }


}
