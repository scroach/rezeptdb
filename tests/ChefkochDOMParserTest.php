<?php

namespace App\Tests;

use App\Service\ChefkochDOMParser;
use PHPUnit\Framework\TestCase;

class ChefkochDOMParserTest extends TestCase {

	function testUrls() {
		$parser = new ChefkochDOMParser();
		self::assertTrue($parser->isApplicableForUrl('https://www.chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('http://www.chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('https://chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('http://chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('www.chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('//www.chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));
		self::assertTrue($parser->isApplicableForUrl('//chefkoch.de/rezepte/2708511423773201/Brioche-Burger-Bun.html'));

		self::assertFalse($parser->isApplicableForUrl('https://www.chefkoch.de/rs/s0g66/Backrezepte.html'));
		self::assertFalse($parser->isApplicableForUrl('https://www.ichkoche.at/nudeln-mit-baerlauch-und-thunfisch-rezept-223070'));
	}
}
