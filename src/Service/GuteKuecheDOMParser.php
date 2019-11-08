<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class GuteKuecheDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return preg_match('/^(https?\:)?(\/\/)?(www\.)?gutekueche\.at/', $url);
	}

	protected function fetchImages(Crawler $doc): array {
		$images = $doc->filter('header img')->extract(['src']);
		foreach ($images as &$image) {
			if(substr($image, 0, 4) === '/img') {
				$image = 'https://www.gutekueche.at'.$image;
			}
		}
		return $images;
	}

	protected function fetchDescription(Crawler $doc): string {
		return $doc->filter('.rezept-preperation')->text();
	}

	protected function fetchIngredients(Crawler $doc): array {
		$ingredients = $doc->filter('.recipe-ingredients table tr')->each(function (Crawler $element, $i) {
			return [
				'amount' => self::convertWhitespaceTrim(
					$element->filter('td')->text().' '.
					$element->filter('th')->first()->text()
				),
				'label' => self::convertWhitespaceTrim($element->filter('th')->last()->text()),
			];
		});

		return $ingredients;
	}

	protected function fetchTitle(Crawler $doc): string {
		return $doc->filter('article h1')->text();
	}

    protected function fetchEffort(Crawler $doc): int {
        return 1;
    }

}
