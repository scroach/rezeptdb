<?php

namespace App\DTO;


use App\Entity\IngredientGroup;

class IngredientGroupDTO {
	/** @var string */
	public ?string $label = null;

	/** @var string[] */
	public array $ingredients = [];

	function __construct() {
	}

	public static function createFromIngredientGroup(IngredientGroup $ingredientGroup): self {
		$group = new self();
		$group->label = $ingredientGroup->getLabel();
		foreach ($ingredientGroup->getIngredients() as $ingredient) {
			array_push($group->ingredients, $ingredient->getLabel());
		}
		return $group;
	}

}