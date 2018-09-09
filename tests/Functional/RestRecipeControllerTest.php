<?php

namespace App\Tests\Functional;

class RestRecipeControllerTest extends AbstractWebTestCase
{

    public function testGetRecipes()
    {
        $this->client->request('GET', '/api/recipes');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = $this->client->getResponse()->getContent();
        self::assertJson($response);
        $data = json_decode($response, true);
        self::assertEquals('RezeptFixed', $data[0]['label']);
        self::assertEquals('123 test', $data[0]['description']);
        self::assertEquals(999, $data[0]['effort']);
        self::assertEquals([], $data[0]['tags']);
        self::assertEquals([], $data[0]['ingredient_groups']);
    }

}
