<?php


namespace App\Service;

class ChefkochDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return preg_match('/^(https?\:)?(\/\/)?(www\.)?chefkoch\.de\/rezepte/', $url);
	}

	protected function fetchImages(\DOMDocument $doc): array {
		$images = [];

		/** @var $image \DOMElement */
		foreach ($doc->getElementsByTagName('a') as $image) {
			if (strpos($image->getAttribute('class'), 'slideshow-imagelink') !== false) {
				if ($image->getAttribute('href')) {
					$images[] = $image->getAttribute('href');
				}
			}
		}
		return $images;
	}

	protected function fetchDescription(\DOMDocument $doc): string {
		try {
			return trim($doc->getElementById('rezept-zubereitung')->nodeValue);
		} catch (\Throwable $e) {
			return 'error trying to parse description';
		}
	}

	protected function fetchIngredients(\DOMDocument $doc): array {
		$ingredients = [];

		/** @var $image \DOMElement */
		foreach ($doc->getElementsByTagName('table') as $image) {
			if (strpos($image->getAttribute('class'), 'incredients') !== false) {
				/** @var $row \DOMElement */
				foreach ($image->getElementsByTagName('tr') as $row) {
					$ingredients[] = [
						'amount' => trim($row->getElementsByTagName('td')->item(0)->nodeValue),
						'label' => trim($row->getElementsByTagName('td')->item(1)->nodeValue)
					];
				}
			}
		}
		return $ingredients;
	}

	protected function fetchTitle(\DOMDocument $doc): string {
		/** @var $image \DOMElement */
		foreach ($doc->getElementsByTagName('h1') as $heading) {
			if (strpos($heading->getAttribute('class'), 'page-title') !== false) {
				return $heading->nodeValue;
			}
		}
	}

}
