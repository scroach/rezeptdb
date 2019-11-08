<?php

namespace App\Tests\Unit;

use App\Service\GuteKuecheDOMParser;
use PHPUnit\Framework\TestCase;

class GuteKuecheDOMParserTest extends TestCase {

	function testUrls() {
		$parser = new GuteKuecheDOMParser();
		self::assertTrue($parser->isApplicableForUrl('https://www.gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('https://gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('http://www.gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('http://gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('www.gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('//www.gutekueche.at/grundrezept-pizzateig-rezept-2589'));
		self::assertTrue($parser->isApplicableForUrl('//gutekueche.at/grundrezept-pizzateig-rezept-2589'));
	}

	function testPizza() {
		// use internal errors so libxml won't throw php warnings/errors on non-wellformed docs
		libxml_use_internal_errors(true);

		$parser = new GuteKuecheDOMParser();
		$result = $parser->analyzeUrl('https://www.gutekueche.at/grundrezept-pizzateig-rezept-2589');

		self::assertContains('https://www.gutekueche.at/img/rezept/2589/grundrezept-pizzateig.jpg', $result['images']);

		$ingredients = [
			["amount" => "1 Wf", "label" => "Hefe (oder Trockenhefe)"],
			["amount" => "250 ml", "label" => "lauwarmes Wasser"],
			["amount" => "500 g", "label" => "Mehl (Type 405)"],
			["amount" => "2 EL", "label" => "Olivenöl (od. Speiseöl)"],
			["amount" => "1 TL", "label" => "Salz"],
			["amount" => "1 Prise", "label" => "Zucker"]
		];

		self::assertEquals('Grundrezept Pizzateig', $result['title']);
		self::assertEquals($ingredients, $result['ingredients']);

		self::assertContains('Das Wasser (hier kann man auch noch einen Schuss Milch hinzufügen', $result['description']);
		self::assertContains('Den fertigen Teig zudecken (mit einem Küchentuch) und für', $result['description']);
		libxml_clear_errors();
	}

}
