<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class GenericWordpressDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return true;
	}

	protected function fetchImages(Crawler $doc): array {
		return $doc->filter('.entry img')->extract('src');
	}

	protected function fetchDescription(Crawler $doc): string {
		return $doc->filter('.entry')->text();
	}

	protected function fetchTitle(Crawler $doc): string {
		return $doc->filter('h2.pagetitle')->text();
	}

	protected function fetchIngredients(Crawler $doc): array {
		return [];
	}

    protected function fetchEffort(Crawler $doc): int {
        return 1;
    }
}
