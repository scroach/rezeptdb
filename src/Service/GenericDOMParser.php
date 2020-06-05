<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class GenericDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return true;
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

	protected function fetchDescription(Crawler $doc): string {
		if ($doc->filter('.entry')->count()) {
			return $doc->filter('.entry')->text();
		} else {
			return '';
		}
	}

	protected function fetchTitle(Crawler $doc): string {
		return '';
	}

	protected function fetchIngredients(Crawler $doc): array {
		return [];
	}

    protected function fetchEffort(Crawler $doc): int {
        return 1;
    }

	private function extractBackgroundImageUrls($doc): array {
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

	private function extractImagesFromSrcAttribute($doc): array {
		return $doc->filter('img')->extract('src');
	}

	private function filterImages($imgUrl) {
		$imgSize = getimagesize($imgUrl);
		$width = $imgSize[0];
		$height = $imgSize[1];
		$imageSizeBigger100Pixels = $width > 100 && $height > 100;
		$aspectRatioOkay = max($width, $height) / min($width, $height) < 3;
		return $imageSizeBigger100Pixels && $aspectRatioOkay;
    }

}
