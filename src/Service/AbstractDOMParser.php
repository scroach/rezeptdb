<?php


namespace App\Service;


use Symfony\Component\DomCrawler\Crawler;

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

	private function fetchDOM($url): Crawler {
		return new Crawler(file_get_contents($url));

	}

	protected abstract function fetchImages(Crawler $doc): array;

	protected abstract function fetchTitle(Crawler $doc): string;

	protected abstract function fetchIngredients(Crawler $doc): array;

	protected abstract function fetchDescription(Crawler $doc): string;

	protected abstract function fetchEffort(Crawler $doc): int;

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
		$string =  preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
		$string =  preg_replace('/^[\s]+|[\s]+$/u', '', $string);
		return $string;
	}

	public static function convertWhitespace($string) {
		$string = preg_replace('/[\pZ\pC]+/u',' ',$string);
		$string = preg_replace('/[\s]+/u',' ',$string);
		return $string;
	}

	public static function convertWhitespaceTrim($string) {
		return self::trim(self::convertWhitespace($string));
	}

}
