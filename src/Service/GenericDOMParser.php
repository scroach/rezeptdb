<?php


namespace App\Service;

use App\DTO\RecipeDTO;
use Symfony\Component\DomCrawler\Crawler;

class GenericDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return true;
	}

	public function extractRecipe(string $url, ?Crawler $doc = null): ?RecipeDTO {
		$doc = $doc ?? $this->fetchDOM($url);

		$result = new RecipeDTO();

		$result->label = $this->fetchTitle($doc);
		$result->description = $this->fetchDescription($doc);
		$result->ingredientGroups = $this->fetchIngredients($doc);
		$result->effort = $this->fetchEffort($doc);

		return $result;
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

}
