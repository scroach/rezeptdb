<?php


namespace App\Service;

use App\DTO\IngredientGroupDTO;
use App\DTO\RecipeDTO;
use Symfony\Component\DomCrawler\Crawler;

/**
 * This parser is based on the fact that most sites want to comply to Google standards and therefore provide
 * standardised data that is easy parsable.
 * Most sites provided their recipe data in the JSON-LD format which contains properties based on the schema.org definition.
 *
 * @see https://schema.org/Recipe
 * @see https://developers.google.com/search/docs/data-types/recipe?hl=de#recipe-properties
 * @see https://json-ld.org/
 */
class JsonLdParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return true;
	}

	public function extractRecipe(string $url, ?Crawler $doc = null): ?RecipeDTO {
		$doc = $doc ?? $this->fetchDOM($url);

		$result = new RecipeDTO();

		$jsonLdData = $this->extractJsonLdData($doc);
		if (!$jsonLdData) {
			return null;
		}

		$result->label = $this->extractTitleFromJsonLdData($jsonLdData);
		$result->description = $this->extractDescriptionFromJsonLdData($jsonLdData);
		$result->ingredientGroups = $this->extractIngredientsFromJsonLdData($jsonLdData);
		$result->effort = $this->extractEffortFromJsonLdData($jsonLdData);
		$result->images = $this->fetchImages($doc, $url);

		return $result;
	}

	protected function fetchEffort(Crawler $doc): int {
		return 1;
	}

	private function extractDescriptionFromJsonLdData($jsonLdData): ?string {
		if (isset($jsonLdData['recipeInstructions'])) {
			if (is_string($jsonLdData['recipeInstructions'])) {
				return $jsonLdData['recipeInstructions'];
			} elseif (is_array($jsonLdData['recipeInstructions'])) {
				$plainInstructions = [];
				foreach ($jsonLdData['recipeInstructions'] as $instruction) {
					if ($instruction instanceof \stdClass && get_object_vars($instruction)['@type'] === 'HowToStep') {
						$plainInstructions[] = $instruction->text;
					} else if (is_string($instruction)) {
						$plainInstructions[] = $instruction;
					} else {
						throw new \Exception('unknown instruction format:'.json_encode($instruction));
					}
				}

				$plainInstructions = array_map(fn($instruction) => html_entity_decode($instruction), $plainInstructions);
				return implode("\r\n\r\n", $plainInstructions);
			} else {
				throw new \Exception('unknown format:'.json_encode($jsonLdData['recipeInstructions']));
			}
		} else {
			return null;
		}
	}

	private function extractTitleFromJsonLdData($jsonLdData): ?string {
		return $jsonLdData['name'] ?? null;
	}

	private function extractIngredientsFromJsonLdData($jsonLdData): array {
		if (isset($jsonLdData['recipeIngredient'])) {
			$ingredientGroup = new IngredientGroupDTO();
			$ingredientGroup->ingredients = array_map(fn($ingredient) => $ingredient, $jsonLdData['recipeIngredient']);
			return [$ingredientGroup];
		} else {
			return [];
		}
	}

	private function extractJsonLdData(Crawler $doc): ?array {
		// https://developers.google.com/search/docs/guides/intro-structured-data?hl=de
		$jsonData = $doc->filter('[type="application/ld+json"]')->extract('_text');
		foreach ($jsonData as $jsonString) {
			$json = json_decode($jsonString);
			$jsonArray = is_array($json) ? $json : [$json];

			foreach ($jsonArray as $singleJsonLd) {
				$singleJsonLd = (array)$singleJsonLd;
				if (isset($singleJsonLd['@type']) && $singleJsonLd['@type'] === 'Recipe') {
					return $singleJsonLd;
				}
			}
		}
		return null;
	}

	private function extractEffortFromJsonLdData($jsonLdData): ?int {
		if (isset($jsonLdData['totalTime'])) {
			$totalTime = new \DateInterval($jsonLdData['totalTime']);
			return $this->dateIntervalToMinutes($totalTime);
		} else {
			$prepTime = new \DateInterval($jsonLdData['prepTime'] ?? '');
			$cookTime = new \DateInterval($jsonLdData['cookTime'] ?? '');
			return $this->dateIntervalToMinutes($prepTime) + $this->dateIntervalToMinutes($cookTime);
		}
	}

	private function dateIntervalToMinutes(\DateInterval $interval): int {
		$intervalInSeconds = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
		return $intervalInSeconds / 60;

	}

}
