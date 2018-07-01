<?php


namespace App\Service;


abstract class AbstractDOMParser {

	public function analyzeUrl($url) {
		$doc = $this->fetchDOM($url);

		$images = $this->fixImageUrls($this->fetchImages($doc));
		$ingredients = $this->fetchIngredients($doc);
		$description = $this->fetchDescription($doc);
		$title = $this->fetchTitle($doc);
		$effort = $this->fetchEffort($doc);

		return [
			'images' => $images,
			'ingredients' => $ingredients,
			'description' => $description,
			'title' => $title,
            'effort' => $effort
		];
	}

	private function fetchDOM($url): \DOMDocument {
		$doc = new \DOMDocument();
		$doc->loadHTML(file_get_contents($url));
		return $doc;
	}

	protected abstract function fetchImages(\DOMDocument $doc): array;

	protected abstract function fetchTitle(\DOMDocument $doc): string;

	protected abstract function fetchIngredients(\DOMDocument $doc): array;

	protected abstract function fetchDescription(\DOMDocument $doc): string;

	protected abstract function fetchEffort(\DOMDocument $doc): int;

	public abstract function isApplicableForUrl(string $url): bool;

	/**
	 * Prepend https: if URL starts with // which causes errors when trying to fetch remote image
	 * @param String[] $imageUrls
	 * @return array
	 */
	private function fixImageUrls($imageUrls) {
		foreach ($imageUrls as &$imageUrl) {
			if (strpos($imageUrl, '//') === 0) {
				$imageUrl = 'https:'.$imageUrl;
			}
		}
		return $imageUrls;
	}

	/**
	 * Strips unicode whitespace chars too unlike php's internal trim method: https://stackoverflow.com/a/4167053/2424814
	 * @param $string
	 * @return null|string|string[]
	 */
	public static function trim($string) {
		return preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
	}

	public static function convertWhitespace($string) {
		return preg_replace('/[\pZ\pC]/u',' ',$string);
	}

	public static function convertWhitespaceTrim($string) {
		return self::trim(self::convertWhitespace($string));
	}

}
