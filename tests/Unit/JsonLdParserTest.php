<?php

namespace App\Tests\Unit;

use App\DTO\IngredientGroupDTO;
use App\Service\JsonLdParser;
use PHPUnit\Framework\TestCase;

class JsonLdParserTest extends TestCase {

	function testChefkoch() {
		$parser = new JsonLdParser();
		$recipe = $parser->extractRecipe('https://www.chefkoch.de/rezepte/1112191217260468/Nudelteig.html');
		self::assertEquals('Nudelteig', $recipe->label);
		self::assertStringStartsWith('Für einen Nudelteig rechnet man pro Person ein Eigelb + das Vollei', $recipe->description);
		self::assertStringEndsWith('anschließend zum Abtropfen auf ein Tuch legen. Gutes Gelingen!', $recipe->description);

		$expectedGroup = new IngredientGroupDTO();
		$expectedGroup->ingredients = [
			'4  Eigelb',
			'1  Ei(er)',
			'2 EL Olivenöl',
			' Salz',
			'400 g Mehl , ca.',
		];

		self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
	}

	function testGuteKueche() {
		$parser = new JsonLdParser();
		$recipe = $parser->extractRecipe('https://www.gutekueche.at/nudelteig-pastateig-rezept-5085');
		self::assertEquals('Nudelteig - Pastateig', $recipe->label);
		self::assertStringStartsWith('FÜr das Nudelteig Rezept das Mehl wie einen Berg auf die Arbeitsfläche', $recipe->description);
		self::assertStringEndsWith('Kurz trocknen lassen und dann in kochendem Wasser bissfest kochen.', $recipe->description);

		$expectedGroup = new IngredientGroupDTO();
		$expectedGroup->ingredients = [
			'3 Stk Eier (Bio-Freiland)',
			'250 g Mehl (griffig)'
		];

		self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
	}

	function testBbqPit() {
		$parser = new JsonLdParser();
		$recipe = $parser->extractRecipe('https://bbqpit.de/rezepte/pork-belly-burnt-ends/');
		self::assertEquals('Pork Belly Burnt Ends', $recipe->label);
		self::assertStringStartsWith('Zunächst muss der Schweinebauch vorbereitet werden. Wenn', $recipe->description);
		self::assertStringEndsWith('wo sie nochmal rund 15 Minuten verweilen und die Sauce besser anziehen kann.', $recipe->description);

		$expectedGroup = new IngredientGroupDTO();
		$expectedGroup->ingredients = [
			'2,5 kg Schweinebauch am Stück',
			'200 ml BBQ-Sauce ((z.B. Sweet Baby Rays Honey Chipotle))',
			'100 g brauner Zucker',
			'100 g Honig',
			'80 g Pit Powder BBQ-Rub',
			'75 g Butter'
		];

		self::assertEquals([$expectedGroup], $recipe->ingredientGroups);
	}

}
