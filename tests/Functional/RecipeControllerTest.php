<?php

namespace App\Tests\Functional;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RecipeControllerTest extends AbstractWebTestCase
{

    public function testIndexAction()
    {
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDelete()
    {
    	$this->loginRick();
        $this->client->request('GET', '/recipes/delete/1');
        self::assertTrue($this->client->getResponse()->isRedirect('/'));
    }

    public function testDeleteRicksRecipeAsMortyNotAllowed()
    {
    	$this->expectException(AccessDeniedException::class);
    	$this->loginMorty();
        $this->client->request('GET', '/recipes/delete/1');
    }

    public function testLoadMoreRecipes()
    {
        /** @var Crawler $crawler */
        $url = '/recipes/loadMoreRecipes';
        $crawler = $this->client->xmlHttpRequest('GET', $url);
        self::assertEquals(20, $crawler->filter('.ui.card')->count());
        self::assertEquals(1, $crawler->filter('.ui.card')->first()->attr('data-recipe-id'));

        $crawler = $this->client->xmlHttpRequest('GET', $url, array('excludeIds' => range(1, 10)));
        self::assertEquals(20, $crawler->filter('.ui.card')->count());
        self::assertEquals(101, $crawler->filter('.ui.card')->first()->attr('data-recipe-id'));

        $crawler = $this->client->xmlHttpRequest('GET', $url, array('excludeIds' => range(1, 1000)));
        self::assertEquals(0, $crawler->filter('.ui.card')->count());
        self::assertEquals('{"message":"Keine Rezepte mehr :("}', $this->client->getResponse()->getContent());
    }

    public function testListTags()
    {
        $this->client->request('GET', '/recipes/tags');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testShowByTag()
    {
        $this->client->request('GET', '/recipes/tags/bacon');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testShowByInvalidTag()
    {
        $this->client->request('GET', '/recipes/tags/pinkFluffyUnicorns');
        self::assertTrue($this->client->getResponse()->isRedirect('/recipes/tags'));
        $this->client->followRedirect();
        $this->assertErrorMessage('Dieser Tag existiert leider nicht!');
    }

    public function testAdd()
    {
        $this->client->request('GET', '/recipes/add');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $values = [
            'images[]' => 'https://images.ichkoche.at/data/image/variations/496x384/2/paprikahendl-rezept-img-19954.jpg',
            'form' => [
                'tagsString' => 'Unicorn,Pink',
                'label' => 'Unicorn Muffins',
                'description' => 'Mix together and enjoy <3',
                'effort' => '1337',
                'ingredientGroups' => [[
                    'label' => 'Topping',
                    'ingredients' => [
                        ['label' => 'Cream'],
                        ['label' => 'Suagr'],
                    ]], [
                    'label' => 'Dough',
                    'ingredients' => [
                        ['label' => 'Love'],
                    ]]]
            ]
        ];
        $this->client->request('POST', '/recipes/add', $values);
        self::assertTrue($this->client->getResponse()->isRedirect());

        $crawler =$this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSuccessMessage('Rezept erfolgreich gespeichert!');

        self::assertEquals(['Unicorn', 'Pink'],$crawler->filter('h1')->siblings()->filter('.label')->extract(['_text']));
    }

    public function testSearch()
    {
        $crawler =$this->client->request('GET', '/recipes/search/TestRecipeSearchDescription');
        self::assertEquals(30, $crawler->filter('.ui.card')->count());
    }

    public function testSearchReturnsSingle()
    {
        // label
        $crawler =$this->client->request('GET', '/recipes/search/UnicornLasagna');
        self::assertEquals(1, $crawler->filter('.ui.card')->count());
        // description
        $crawler =$this->client->request('GET', '/recipes/search/nooodles');
        self::assertEquals(1, $crawler->filter('.ui.card')->count());
        // ingredient
        $crawler =$this->client->request('GET', '/recipes/search/beans');
        self::assertEquals(1, $crawler->filter('.ui.card')->count());
    }

    public function testSearchNothingFound()
    {
        $crawler =$this->client->request('GET', '/recipes/search/SomeWeirdInvalidParameter');
        self::assertEquals(0, $crawler->filter('.ui.card')->count());
        $this->assertWarningMessage('Ich konnte leider keine Rezepte mit "SomeWeirdInvalidParameter" finden :\'(');
    }


}
