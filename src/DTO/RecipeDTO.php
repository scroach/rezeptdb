<?php
namespace App\DTO;

use App\Entity\Image;
use App\Entity\Recipe;

class RecipeDTO
{
    /** @var string */
    public $label;

    /** @var int */
    public $effort;

    /** @var string */
    public $description;

    /** @var string[] */
    public $tags = array();

    /** @var IngredientGroupDTO[] */
    public $ingredientGroups = array();

    /** @var string */
    public $image;

    function __construct(Recipe $recipe) {
        $this->label = $recipe->getLabel();
        $this->effort = $recipe->getEffort();
        $this->description = $recipe->getDescription();
        foreach ($recipe->getTags() as $tag) {
            array_push($this->tags, $tag->getLabel());
        }
        foreach ($recipe->getIngredientGroups() as $ingredientGroup) {
            array_push($this->ingredientGroups, new IngredientGroupDTO($ingredientGroup));
        }
        if ($recipe->getImages()->count() > 0) {
           $this->image = $recipe->getImages()->get(0)->getUrl();
        }
    }

}