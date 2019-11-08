<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class IchKocheDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return preg_match('/^(https?\:)?(\/\/)?(www\.)?ichkoche\.at/', $url);
	}

	protected function fetchImages(Crawler $doc): array {
		$images = $doc->filter('.gallery_slider.recipe img')->extract('src');

		return $images;
	}

	protected function fetchDescription(Crawler $doc): string {
		return $doc->filter('[itemprop="recipeInstructions"]')->text();
	}

	protected function fetchIngredients(Crawler $doc): array {
		$ingredients = $doc->filter('.ingredient')->each(function (Crawler $element, $i) {
			return [
				'amount' => self::convertWhitespaceTrim($element->filter('.amount')->text()),
				'label' => self::convertWhitespaceTrim($element->filter('.name')->text()),
			];
		});

		return $ingredients;
	}

	protected function fetchTitle(Crawler $doc): string {
		return $doc->filter('.main_content h1')->text();
	}

    protected function fetchEffort(Crawler $doc): int {
        return 1;
    }

}
