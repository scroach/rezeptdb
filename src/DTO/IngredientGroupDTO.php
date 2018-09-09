<?php
namespace App\DTO;


use App\Entity\Ingredient;
use App\Entity\IngredientGroup;
use App\Entity\Recipe;

class IngredientGroupDTO
{
    /** @var string */
    public $label;

    /** @var string[] */
    public $ingredients = array();

    function __construct(IngredientGroup $ingredientGroup) {
        $this->label = $ingredientGroup->getLabel();
        foreach ($ingredientGroup->getIngredients() as $ingredient) {
            array_push($this->ingredients, $ingredient->getLabel());
        }
    }

}