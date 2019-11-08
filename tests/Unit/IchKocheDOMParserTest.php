<?php

namespace App\Tests\Unit;

use App\Service\IchKocheDOMParser;
use PHPUnit\Framework\TestCase;

class IchKocheDOMParserTest extends TestCase {

	function testUrls() {
		$parser = new IchKocheDOMParser();
		self::assertTrue($parser->isApplicableForUrl('https://www.ichkoche.at/bananen-tiramisu-rezept-233360'));
		self::assertTrue($parser->isApplicableForUrl('https://ichkoche.at/bananen-tiramisu-rezept-233360'));
		self::assertTrue($parser->isApplicableForUrl('http://www.ichkoche.at/bananen-tiramisu-rezept-233360'));
		self::assertTrue($parser->isApplicableForUrl('http://ichkoche.at/bananen-tiramisu-rezept-233360'));
		self::assertTrue($parser->isApplicableForUrl('www.ichkoche.at/bananen-tiramisu-rezept-233360'));
		self::assertTrue($parser->isApplicableForUrl('ichkoche.at/bananen-tiramisu-rezept-233360'));
	}

	function testTiramisu() {
		// use internal errors so libxml won't throw php warnings/errors on non-wellformed docs
		libxml_use_internal_errors(true);

		$parser = new IchKocheDOMParser();
		$result = $parser->analyzeUrl('https://www.ichkoche.at/bananen-tiramisu-rezept-233360');

		self::assertContains('https://images.ichkoche.at/data/image/variations/496x384/12/bananen-tiramisu-img-116921.jpg', $result['images']);

		$ingredients = [
			["amount" => "3", "label" => "Bananen"],
			["amount" => "200 g", "label" => "Mascarpone"],
			["amount" => "4 EL", "label" => "Feinkristallzucker"],
			["amount" => "1 Pkg.", "label" => "Vanillezucker"],
			["amount" => "250 ml", "label" => "Schlagobers"],
			["amount" => "1 Pkg.", "label" => "Sahnesteif"],
			["amount" => "40", "label" => "Biskotten"],
			["amount" => "250 ml", "label" => "Kakao"],
			["amount" => "", "label" => "Kakaopulver"],
		];

		self::assertEquals('Bananen-Tiramisu', $result['title']);
		self::assertEquals($ingredients, $result['ingredients']);

		self::assertContains('Bananen in Scheiben schneiden.', $result['description']);
		self::assertContains('Feinkristallzucker und Vanillezucker mit Mascarpone verrühren', $result['description']);
		self::assertContains('Mit der restlichen Mascarponecreme abschließen. Zum Schluss mit etwas Kakaopulver bestreuen', $result['description']);
		libxml_clear_errors();
	}

}
