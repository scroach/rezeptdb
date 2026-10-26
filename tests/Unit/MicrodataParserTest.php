<?php

namespace App\Tests\Unit;

use App\DTO\IngredientGroupDTO;
use App\DTO\RecipeDTO;
use App\Service\MicrodataParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class MicrodataParserTest extends TestCase
{
    private MicrodataParser $parser;

    public function setUp(): void
    {
        $this->parser = new MicrodataParser();
    }

    public function testIchKoche(): void
    {
        $recipe = $this->parser->extractRecipe('https://www.ichkoche.at/palatschinken-grundrezept-rezept-4273');
        self::assertStringContainsString('Palatschinken', $recipe->label);
        self::assertStringStartsWith('Für den Teig die Eier aufschlagen und gut verquirlen', $recipe->description);
        self::assertStringEndsWith('Die fertigen Palatschinken bis zu Füllung warm stellen.', $recipe->description);

        $expectedGroup = new IngredientGroupDTO();
        $expectedGroup->ingredients = [
            '150 g Mehl',
            '2 Stk. Eier',
            '250 ml Milch',
            '1 EL Butter',
            '1 Prise Salz',
            '4 EL Butter',
        ];

        self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
    }

// currently not working
//    public function testGusto(): void
//    {
//        $recipe = $this->parser->extractRecipe('https://www.gusto.at/rezepte/spanische-erdaepfelkroketten-schinken');
//        self::assertEquals('Spanische Erdäpfelkroketten mit Schinken', $recipe->label);
//        self::assertStringStartsWith('Erdäpfel in Salzwasser kochen, abseihen und ausdampfen lassen. Schinken möglichst klein schneiden', $recipe->description);
//        self::assertStringEndsWith('Kroketten an den Enden mit je einer halben Schinkenscheibe umwickeln.', $recipe->description);
//
//        $expectedGroup = new IngredientGroupDTO();
//        $expectedGroup->ingredients = [
//            'mehlige Erdäpfel',
//            'Rohschinken',
//            'griffiges Mehl',
//            'Dotter',
//            'Rohschinken (dünn geschnitten)',
//            'griffiges Mehl',
//            'Ei (verquirlt)',
//            'Semmelbrösel',
//            'Salz',
//            'Pfeffer',
//            'Mehl',
//            'Öl zum Backen',
//        ];
//
//        self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
//    }
}
