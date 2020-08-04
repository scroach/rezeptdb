<?php


namespace App\Service;

use App\DTO\IngredientGroupDTO;
use App\DTO\RecipeDTO;
use Symfony\Component\DomCrawler\Crawler;

/**
 * This parser is based on the fact that most sites want to comply to Google standards and therefore provide
 * standardised data that is easy parsable.
 * This parser is based on the 'microdata' approach which is basically tagging DOM elements with attributes.
 *
 * @see https://developers.google.com/search/docs/guides/intro-structured-data?hl=de
 * @see https://www.w3.org/TR/microdata/
 */
class MicrodataParser extends AbstractDOMParser {


	// itemprop="totalTime
	// itemprop="name
	// itemprop="recipeInstructions

	public function isApplicableForUrl(string $url): bool {
		return true;
	}

	public function extractRecipe(string $url, ?Crawler $doc = null): ?RecipeDTO {
		$doc = $doc ?? $this->fetchDOM($url);

		$result = new RecipeDTO();

		$result->label = $this->extractTitle($doc);
		$result->description = $this->extractDescription($doc);
		$result->ingredientGroups = $this->extractIngredients($doc);
		$result->effort = $this->extractEffortFromJsonLdData($doc);
		$result->images = $this->fetchImages($doc, $url);

		return $result;
	}

	protected function fetchEffort(Crawler $doc): int {
		return implode("\r\n", $doc->filter('[itemprop="totalTime"]')->extract(['content']));
	}

	private function extractDescription(Crawler $doc): ?string {
		$instructions = [
			...$doc->filter('[itemprop="recipeInstructions"]')->extract(['content']),
			...$doc->filter('[itemprop="recipeInstructions"]')->extract(['_text']),
		];
		return implode("\r\n", array_map(fn(string $desc) => self::trim($desc), array_filter($instructions)));
	}

	private function extractTitle(Crawler $doc): ?string {
		$title = [
			...$doc->filter('[itemprop="name"]')->extract(['content']),
			...$doc->filter('[itemprop="name"]')->extract(['_text']),
		];
		return implode(' ', array_filter($title));
	}

	private function extractIngredients(Crawler $doc): array {
		$ingredients = [
			...$doc->filter('[itemprop="recipeIngredient"]')->extract(['content']),
			...$doc->filter('[itemprop="recipeIngredient"]')->extract(['_text']),
			...$doc->filter('[itemprop="ingredients"]')->extract(['content']),
			...$doc->filter('[itemprop="ingredients"]')->extract(['_text']),
		];

		$ingredientGroup = new IngredientGroupDTO();
		$ingredientGroup->ingredients = [...array_filter(array_map(fn(string $ingredient) => self::trim($ingredient), $ingredients))];
		return [$ingredientGroup];
	}

	private function extractEffortFromJsonLdData(Crawler $doc): ?int {
		$totalTimeNode = $doc->filter('[itemprop="totalTime"]');

		if ($totalTimeNode->count()) {
			$totalTime = implode('', $totalTimeNode->extract(['content'])) ?? implode('', $totalTimeNode->extract(['_text']));
			$totalTime = new \DateInterval($totalTime);
			return $this->dateIntervalToMinutes($totalTime);
		} else {
			return 0;
		}
	}

	private function dateIntervalToMinutes(\DateInterval $interval): int {
		$intervalInSeconds = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
		return $intervalInSeconds/60;

	}

}
