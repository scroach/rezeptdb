<?php


namespace App\Service;


use App\DTO\RecipeDTO;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractDOMParser {

	public abstract function extractRecipe(string $url, ?Crawler $doc = null): ?RecipeDTO;

	protected function fetchDOM($url): Crawler {
		// use internal errors so libxml won't throw php warnings/errors on non-wellformed docs
		libxml_use_internal_errors(true);
		$result = new Crawler(file_get_contents($url));
		libxml_clear_errors();
		return $result;
	}

	public abstract function isApplicableForUrl(string $url): bool;

	/**
	 * Prepend https: if URL starts with // which causes errors when trying to fetch remote image
	 * @param String[] $imageUrls
	 * @param string $baseUrl
	 * @return array
	 */
	public function fixImageUrls(array $imageUrls, string $baseUrl) {
		$parsedBaseUrl = parse_url($baseUrl);

		foreach ($imageUrls as &$imageUrl) {
			if (strpos($imageUrl, '//') === 0) {
				$imageUrl = 'https:'.$imageUrl;
			}
			if (strpos($imageUrl, '/') === 0) {
				$imageUrl = $parsedBaseUrl['scheme'].'://'.$parsedBaseUrl['host'].$imageUrl;
			}
		}
		return $imageUrls;
	}

	/**
	 * Strips unicode whitespace chars too unlike php's internal trim method: https://stackoverflow.com/a/4167053/2424814
	 * @param $string
	 * @return null|string|string[]
	 */
	public static function trimAndRemoveNewline($string) {
		$string = preg_replace("/[\n\r]/", " ", $string);
		$string = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
		$string = preg_replace('/^[\s]+|[\s]+$/u', '', $string);
		return $string;
	}

	public static function convertWhitespace($string) {
		$string = preg_replace('/[\pZ\pC]+/u',' ',$string);
		$string = preg_replace('/[\s]+/u',' ',$string);
		return $string;
	}

	public static function convertWhitespaceTrim($string) {
		return self::trimAndRemoveNewline(self::convertWhitespace($string));
	}

	protected function extractImagesFromSrcAttribute($doc): array {
		return $doc->filter('img')->extract('src');
	}

	protected function extractBackgroundImageUrls($doc): array {
		$imgUrls = [];
		$attributes = $doc->filter('*')->extract(['style']);
		$attributes = array_filter($attributes);
		foreach ($attributes as $attribute) {
			$matches = [];
			$result = preg_match('/background-image:.*url\([\'"](.+)[\'"]\)/i', $attribute, $matches);
			if ($result) {
				$imgUrls[] = $matches[1];
			}
		}
		return $imgUrls;
	}

	protected function fetchImages(Crawler $doc, string $url): array {
		$imgUrls = [
			...$this->extractBackgroundImageUrls($doc),
			...$this->extractImagesFromSrcAttribute($doc),
		];

		$imgUrls = $this->fixImageUrls($imgUrls, $url);
		$imgUrls = array_values(array_filter($imgUrls, [$this, 'filterImages']));

		return $imgUrls;
	}

	protected function filterImages($imgUrl) {
		if(!$imgUrl) {
			return false;
		}

		try {
			$imgSize = getimagesize($imgUrl);
			$width = $imgSize[0];
			$height = $imgSize[1];
			$imageSizeBigger100Pixels = $width > 100 && $height > 100;
			$aspectRatioOkay = max($width, $height) / min($width, $height) < 3;
			return $imageSizeBigger100Pixels && $aspectRatioOkay;
		} catch (\Throwable $exception) {
			return false;
		}
	}

}
