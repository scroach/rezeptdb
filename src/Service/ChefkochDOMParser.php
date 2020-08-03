<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class ChefkochDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return preg_match('/^(https?\:)?(\/\/)?(www\.)?chefkoch\.de\/rezepte/', $url);
	}

	protected function fetchImages(Crawler $doc, string $url): array {
		return $doc->filter('article amp-img')->extract(['src']);
	}

	protected function fetchDescription(Crawler $doc): string {
		try {
			$description = $doc->filter('article .ds-box:not(.recipe-author)')->extract('_text');
			$description = implode("\r\n", $description);
			return $description;
		} catch (\Throwable $e) {
			return 'error trying to parse description';
		}
	}

	protected function fetchIngredients(Crawler $doc): array {
		$ingredients = $doc->filter('table.ingredients tr')->each(function (Crawler $element, $i) {
			return [
				'amount' => self::convertWhitespaceTrim($element->filter('.td-left')->text()),
				'label' => self::convertWhitespaceTrim($element->filter('.td-right')->text()),
			];
		});

		return $ingredients;
	}

	protected function fetchTitle(Crawler $doc): string {
		return $doc->filter('article h1')->text();
	}

    protected function fetchEffort(Crawler $doc): int {
        $effort = 0;

		$preparationInfo =$doc->filter('.recipe-preptime')->text();
        if($preparationInfo) {
            preg_match_all('/(\d*) Min./', $preparationInfo, $minutes);
            preg_match_all('/(\d*) Std./', $preparationInfo, $hours);
            for($i = 1; $i < count($minutes); $i++) {
                if(!empty($minutes[$i])) {
                    $effort += $minutes[$i][0];
                }
            }
            for($i = 1; $i < count($hours); $i++) {
                if(!empty($hours[$i])) {
                    $effort += $hours[$i][0] * 60;
                }
            }
        }
        if($effort == 0) {
            $effort = 1;
        }
        return $effort;
    }

}
