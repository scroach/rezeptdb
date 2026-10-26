<?php

namespace App\Tests\Unit;

use App\DTO\IngredientGroupDTO;
use App\DTO\RecipeDTO;
use App\Service\JsonLdParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class JsonLdParserTest extends TestCase
{
    public function testChefkoch(): void
    {
        $parser = new JsonLdParser();
        $recipe = $parser->extractRecipe('https://www.chefkoch.de/rezepte/1112191217260468/Nudelteig.html');

        self::assertEquals('Nudelteig von chefkoch', $recipe->label);
        self::assertStringStartsWith('Für einen Nudelteig rechnet man pro Person ein Eigelb + das Vollei', $recipe->description);
        self::assertStringEndsWith('anschließend zum Abtropfen auf ein Tuch legen. Gutes Gelingen!', $recipe->description);

        $expectedGroup = new IngredientGroupDTO();
        $expectedGroup->ingredients = [
            '4 Eigelb',
            '1 Ei(er)',
            '2 EL Olivenöl',
            'Salz',
            '400 g Mehl (ca)',
        ];

        self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
    }

    public function testGuteKueche(): void
    {
        $parser = new JsonLdParser();
        $recipe = $parser->extractRecipe('https://www.gutekueche.at/nudelteig-pastateig-rezept-5085');
        self::assertEquals('Nudelteig - Pastateig', $recipe->label);
        self::assertStringStartsWith('Für das <strong>Nudelteig Rezept</strong> das Mehl wie einen Berg', $recipe->description);
        self::assertStringEndsWith('Die Nudeln kurz trocknen lassen und dann in kochendem Wasser (rund 3-4 Minuten) bissfest kochen.', $recipe->description);

        $expectedGroup = new IngredientGroupDTO();
        $expectedGroup->ingredients = [
            '50 g Ei (ca. 1 Stück)',
            '120 g Eidotter (ca. 6 Stück)',
            '4 g Olivenöl',
            '2 g Salz',
            '250 g Weizenmehl (glatt, W480)',
        ];

        self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
    }

    public function testBbqPit(): void
    {
        $parser = new JsonLdParser();
        $recipe = $parser->extractRecipe('https://bbqpit.de/rezepte/pork-belly-burnt-ends/');
        self::assertEquals('Pork Belly Burnt Ends', $recipe->label);
        self::assertStringStartsWith('Zunächst muss der Schweinebauch vorbereitet werden. Wenn', $recipe->description);
        self::assertStringEndsWith('wo sie nochmal rund 15 Minuten verweilen und die Sauce besser anziehen kann.', $recipe->description);

        $expectedGroup = new IngredientGroupDTO();
        $expectedGroup->ingredients = [
            '2 kg Livar Schweinebauch',
            '200 ml BBQ-Sauce',
            '100 g brauner Zucker',
            '100 g Honig',
            '80 g BBQPit Pork Rub',
            '75 g Butter',
        ];

        self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
    }

    public function testTasteOfTravel(): void
    {
        $parser = new JsonLdParser();
        $recipe = $parser->extractRecipe('https://www.tasteoftravel.at/weiche-burger-buns/');
        self::assertEquals('Weiche Burger Buns', $recipe->label);
        self::assertStringStartsWith('Wasser und Mehl in einer kleinen Pfanne oder einem kleinen Topf mit einem Schneebesen klumpenfrei verrühren', $recipe->description);
        self::assertStringEndsWith('Käse, Salat und einer Tomatenscheibe füllen. Gutes Gelingen!', $recipe->description);

        $expectedGroup = new IngredientGroupDTO();
        $expectedGroup->ingredients = [
            '45 ml Wasser',
            '45 ml Milch',
            '16 g Mehl (ca. 1 leicht gehäufter EL)',
            '110 ml warme Milch',
            '2  gehäufte TL (5 g) Trockenhefe oder 15 g frische Hefe (= 1/3 Würfel)',
            '1  Ei (M)',
            '55 g Butter, zerlassen und leicht abgekühlt',
            '25 g feiner Kristallzucker',
            '1,5  gestr. TL (6 g) feines Salz',
            '300 g Weizenmehl (W700, Deutschland: Typ 550)',
            'Zum Bestreichen: 1 Eigelb + 1 EL Milch verquirlt',
            'Öl für die Schüssel, Mehl zum Arbeiten',
        ];

        self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
    }
}
