<?php


namespace App\Service;

class IchKocheDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return preg_match('/^(https?\:)?(\/\/)?(www\.)?ichkoche\.at/', $url);
	}

	protected function fetchImages(\DOMDocument $doc): array {
		$images = [];

		$imageXpath = '//div[@class="image_wrap"]//*[@itemprop="image"]';
		$xpath = new \DOMXPath($doc);

		/** @var $image \DOMElement */
		foreach ($xpath->query($imageXpath) as $image) {
			if ($image->getAttribute('data-src')) {
				$images[] = $image->getAttribute('data-src');
			}
		}
		return $images;
	}

	protected function fetchDescription(\DOMDocument $doc): string {
		try {
			$descriptionXPath = '//div[@itemprop="recipeInstructions"]';
			$xpath = new \DOMXPath($doc);
			if ($xpath->query($descriptionXPath)->length) {
				return trim($xpath->query($descriptionXPath)->item(0)->nodeValue);
			}
		} catch (\Throwable $e) {
			return 'error trying to parse description: '.$e->getMessage();
		}
	}

	protected function fetchIngredients(\DOMDocument $doc): array {
		$ingredients = [];

		/** @var $ingredient \DOMElement */
		foreach ($doc->getElementsByTagName('li') as $ingredient) {
			if (strpos($ingredient->getAttribute('class'), 'ingredient') !== false) {
				$ingredients[] = [
					'amount' => trim($ingredient->getElementsByTagName('span')->item(0)->nodeValue),
					'label' => trim($ingredient->getElementsByTagName('span')->item(1)->nodeValue)
				];
			}
		}
		return $ingredients;
	}

	protected function fetchTitle(\DOMDocument $doc): string {
		/** @var $image \DOMElement */
		foreach ($doc->getElementsByTagName('h1') as $heading) {
			if (strpos($heading->getAttribute('class'), 'page_title') !== false) {
				return $heading->nodeValue;
			}
		}

		return '';
	}

}
