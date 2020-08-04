<?php
namespace App\DTO;

use App\Entity\Image;
use App\Entity\Recipe;

class RecipeDTO
{
	public ?string $label;

    public ?int $effort;

    public ?string $description;

    /** @var string[] */
    public array $tags = [];

    /** @var IngredientGroupDTO[] */
    public array $ingredientGroups = [];

    /** @var string[] */
    public array $images = [];

    function __construct() {
    }

	public static function createFromRecipe(Recipe $recipe): self {
    	$recipeDto = new self();
		$recipeDto->label = $recipe->getLabel();
		$recipeDto->effort = $recipe->getEffort();
		$recipeDto->description = $recipe->getDescription();
		foreach ($recipe->getTags() as $tag) {
			array_push($recipeDto->tags, $tag->getLabel());
		}
		foreach ($recipe->getIngredientGroups() as $ingredientGroup) {
			array_push($recipeDto->ingredientGroups, IngredientGroupDTO::createFromIngredientGroup($ingredientGroup));
		}
		if ($recipe->getImages()->count() > 0) {
			$recipeDto->images = $recipe->getImages()->get(0)->getUrl();
		}
		return $recipeDto;
    }

}