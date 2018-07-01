<?php


namespace App\Service;

class GuteKuecheDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return preg_match('/^(https?\:)?(\/\/)?(www\.)?gutekueche\.at/', $url);
	}

	protected function fetchImages(\DOMDocument $doc): array {
		preg_match('/var imgs = (.*)/', $doc->saveXML(), $matches);
		$images = $matches[1];
		if (substr($images, -1) === ',') {
			$images = substr($images, 0, -1);
		}

		$imageArray = json_decode($images);
		foreach ($imageArray as &$image) {
			$image = 'https://www.gutekueche.at/'.$image;
		}
		return $imageArray;
	}

	protected function fetchDescription(\DOMDocument $doc): string {
		try {
			$descriptionXPath = '//section[@itemprop="recipeInstructions"]/ol/li';
			$xpath = new \DOMXPath($doc);
			$description = '';
			foreach ($xpath->query($descriptionXPath) as $descriptionParagraph) {
				$description .= $descriptionParagraph->nodeValue."\r\n";
			}
			return $description;
		} catch (\Throwable $e) {
			return 'error trying to parse description: '.$e->getMessage();
		}
	}

	protected function fetchIngredients(\DOMDocument $doc): array {
		$ingredients = [];

		/** @var $ingredient \DOMElement */
		foreach ($doc->getElementsByTagName('tr') as $ingredient) {
			if (strpos($ingredient->getAttribute('itemprop'), 'ingredients') !== false) {
				$ingredients[] = [
					'amount' => trim($ingredient->getElementsByTagName('td')->item(0)->nodeValue).' '.
						trim($ingredient->getElementsByTagName('th')->item(0)->nodeValue),
					'label' => trim($ingredient->getElementsByTagName('th')->item(1)->nodeValue)
				];
			}
		}
		return $ingredients;
	}

	protected function fetchTitle(\DOMDocument $doc): string {
		/** @var $image \DOMElement */
		foreach ($doc->getElementsByTagName('h1') as $heading) {
			if (strpos($heading->getAttribute('itemprop'), 'name') !== false) {
				return $heading->nodeValue;
			}
		}

		return '';
	}

    protected function fetchEffort(\DOMDocument $doc): int {
        return 1;
    }

}
