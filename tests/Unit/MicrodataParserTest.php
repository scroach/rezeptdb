<?php

namespace App\Tests\Unit;

use App\DTO\IngredientGroupDTO;
use App\Service\JsonLdParser;
use App\Service\MicrodataParser;
use PHPUnit\Framework\TestCase;

class MicrodataParserTest extends TestCase {
	
	private MicrodataParser $parser ;

	public function setUp():void {
		$this->parser = new MicrodataParser();
	}

	function testIchKoche() {
		$recipe = $this->parser->extractRecipe('https://www.ichkoche.at/palatschinken-grundrezept-rezept-4273');
		self::assertStringContainsString('Palatschinken', $recipe->label);
		self::assertStringStartsWith('Für den Teig die Eier aufschlagen und gut verquirlen', $recipe->description);
		self::assertStringEndsWith('Mehr Palatschinken Rezepte in süßer und pikanter Variante.', $recipe->description);

		$expectedGroup = new IngredientGroupDTO();
		$expectedGroup->ingredients = [
			'150 g Mehl (glatt)',
            '2 Stk. Eier',
            '250 ml Milch',
            '1 EL Butter (geschmolzen)',
            '1 Prise Salz',
            '4 EL Butter (zerlassen, zum Herausbacken)'
		];

		self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
	}

	function testTasteOfTravel() {
		$recipe = $this->parser->extractRecipe('https://www.tasteoftravel.at/weiche-burger-buns/');
		self::assertEquals('Weiche Burger Buns', $recipe->label);
		self::assertStringStartsWith('Wasser und Mehl in einer kleinen Pfanne oder einem kleinen Topf mit einem Schneebesen klumpenfrei verrühren', $recipe->description);
		self::assertStringEndsWith('Käse, Salat und einer Tomatenscheibe füllen. Gutes Gelingen!', $recipe->description);

		$expectedGroup = new IngredientGroupDTO();
		$expectedGroup->ingredients = [
			'45 ml Wasser',
            '45 ml Milch',
            '16 g Mehl (ca. 1 leicht gehäufter EL)',
            '110 ml warme Milch',
            '2 gehäufte TL (5 g) Trockengerm oder 15 g frischer Germ (= 1/3 Würfel)',
            '1 Ei (M)',
            '55 g Butter, zerlassen und leicht abgekühlt',
            '25 g feiner Kristallzucker',
            '1,5 TL (6 g) feines Salz',
            '300 g Weizenmehl (W700, Deutschland: Typ 550)',
            'Zum Bepinseln: 1 Eigelb + 1 EL Milch verquirlt',
            'Außerdem: Öl für die Schüssel, Mehl zum Arbeiten'
		];

		self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
	}



}
