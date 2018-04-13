<?php


namespace App\Service;


abstract class AbstractDOMParser {

	public function analyzeUrl($url) {
		$doc = $this->fetchDOM($url);

		$images = $this->fetchImages($doc);
		$ingredients = $this->fetchIngredients($doc);
		$description = $this->fetchDescription($doc);

		return [
			'images' => $images,
			'ingredients' => $ingredients,
			'description' => $description,
		];
	}

	private function fetchDOM($url): \DOMDocument {
		$doc = new \DOMDocument();
		$doc->loadHTML(file_get_contents($url));
		return $doc;
	}

	protected abstract function fetchImages(\DOMDocument $doc): array;

	protected abstract function fetchIngredients(\DOMDocument $doc): array;

	protected abstract function fetchDescription(\DOMDocument $doc): string;


}
