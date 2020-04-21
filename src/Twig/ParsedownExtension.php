<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Allows the usage of parsedown in twig templates with activated safe mode so html will be escaped.
 */
class ParsedownExtension extends AbstractExtension {

	public function getFilters() {
		return [
			new TwigFilter('parsedown', [$this, 'parsedown']),
		];
	}

	public function parsedown($text) {
		$parsedown = new \Parsedown();
		$parsedown->setSafeMode(true);
		return $parsedown->text($text);
	}

}