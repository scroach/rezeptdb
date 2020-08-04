<?php


namespace App\Service;


use App\DTO\RecipeDTO;

class RecipeParserService {

	/** @var AbstractDOMParser[] */
	private array $domParsers;

	public function __construct($domParsers) {
		$this->domParsers = $domParsers;
	}

	public function analyze(string $url): ?RecipeDTO {
		$results = [];

		foreach ($this->domParsers as $domParser) {
			if ($domParser->isApplicableForUrl($url)) {
				if ($result = $domParser->extractRecipe($url)) {
					$results[] = $result;
				}
			}
		}

		usort($results, [$this, 'sortRecipeDto']);

		return array_shift($results);
	}

	private function sortRecipeDto(RecipeDTO $a, RecipeDTO $b) {
		return $this->voteRecipeDtoConfience($b) <=> $this->voteRecipeDtoConfience($a);
	}

	/**
	 * Tries to vote on a RecipeDTO extracted from a remote source.
	 * Confidence level starts at 1 and is gradually reduced based on decisions like empty description, empty ingredients etc.
	 * The higher the vote the better.
	 *
	 * @param RecipeDTO $a
	 * @return float
	 */
	private function voteRecipeDtoConfience(RecipeDTO $a): float {
		$confidence = 1;
		if (!$a->description || strlen($a->description) < 3) {
			$confidence *= 0.5;
		}
		if (!$a->ingredientGroups || count($a->ingredientGroups) === 0) {
			$confidence *= 0.5;
		}
		return $confidence;
	}

}