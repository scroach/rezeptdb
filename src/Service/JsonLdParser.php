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
class JsonLdParser extends AbstractDOMParser
{
    public function isApplicableForUrl(string $url): bool
    {
        return true;
    }

    public function extractRecipe(string $url, ?Crawler $doc = null): ?RecipeDTO
    {
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

    protected function fetchEffort(Crawler $doc): int
    {
        return 1;
    }

    private function extractDescriptionFromJsonLdData(array $jsonLdData): ?string
    {
        if (!isset($jsonLdData['recipeInstructions'])) {
            return null;
        }

        $plainInstructions = $this->flattenInstructions($jsonLdData['recipeInstructions']);
        if ($plainInstructions === []) {
            return null;
        }

        $plainInstructions = array_map(
            static fn (string $instruction) => html_entity_decode($instruction, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            $plainInstructions
        );

        return implode("\r\n\r\n", $plainInstructions);
    }

    /**
     * Flattens all supported instruction formats (plain strings, HowToStep and
     * nested HowToSection entries) into a list of instruction texts.
     *
     * @return string[]
     */
    private function flattenInstructions(mixed $data): array
    {
        if (is_string($data)) {
            return [$data];
        }

        if (!is_array($data)) {
            return [];
        }

        $type = $data['@type'] ?? null;
        if ($type === 'HowToStep' && isset($data['text'])) {
            return [(string) $data['text']];
        }

        if (isset($data['itemListElement'])) {
            return $this->flattenInstructions($data['itemListElement']);
        }

        if (isset($data['text']) && is_string($data['text'])) {
            return [$data['text']];
        }

        $instructions = [];
        foreach ($data as $value) {
            $instructions = array_merge($instructions, $this->flattenInstructions($value));
        }

        return $instructions;
    }

    private function extractTitleFromJsonLdData(array $jsonLdData): ?string
    {
        return isset($jsonLdData['name']) ? (string) $jsonLdData['name'] : null;
    }

    private function extractIngredientsFromJsonLdData(array $jsonLdData): array
    {
        if (!isset($jsonLdData['recipeIngredient']) || !is_array($jsonLdData['recipeIngredient'])) {
            return [];
        }

        $ingredientGroup = new IngredientGroupDTO();
        $ingredientGroup->ingredients = array_values(array_map(
            static fn ($ingredient) => (string) $ingredient,
            $jsonLdData['recipeIngredient']
        ));

        return [$ingredientGroup];
    }

    private function extractJsonLdData(Crawler $doc): ?array
    {
        // https://developers.google.com/search/docs/guides/intro-structured-data?hl=de
        $jsonData = $doc->filter('[type="application/ld+json"]')->extract(['_text']);
        foreach ($jsonData as $jsonString) {
            $json = json_decode($jsonString, true);
            if (!is_array($json)) {
                continue;
            }

            $recipe = $this->findRecipe($json);
            if ($recipe !== null) {
                return $recipe;
            }
        }

        return null;
    }

    /**
     * Finds a Recipe entity in a JSON-LD document, including nested @graph
     * structures used by many modern recipe sites.
     */
    private function findRecipe(array $data): ?array
    {
        $type = $data['@type'] ?? null;
        if ($type === 'Recipe' || (is_array($type) && in_array('Recipe', $type, true))) {
            return $data;
        }

        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }

            $recipe = $this->findRecipe($value);
            if ($recipe !== null) {
                return $recipe;
            }
        }

        return null;
    }

    private function extractEffortFromJsonLdData(array $jsonLdData): ?int
    {
        try {
            if (isset($jsonLdData['totalTime'])) {
                $totalTime = new \DateInterval($jsonLdData['totalTime']);

                return $this->dateIntervalToMinutes($totalTime);
            }

            $prepTime = new \DateInterval($jsonLdData['prepTime'] ?? '');
            $cookTime = new \DateInterval($jsonLdData['cookTime'] ?? '');

            return $this->dateIntervalToMinutes($prepTime) + $this->dateIntervalToMinutes($cookTime);
        } catch (\Exception) {
            return null;
        }
    }

    private function dateIntervalToMinutes(\DateInterval $interval): int
    {
        $intervalInSeconds = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();

        return (int) ($intervalInSeconds / 60);
    }
}
