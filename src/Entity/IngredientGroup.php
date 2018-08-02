<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ingredient_groups")
 */
class IngredientGroup {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Recipe", inversedBy="ingredientGroups")
	 */
	private $recipe;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Ingredient", mappedBy="group", cascade={"persist"}, orphanRemoval=true)
	 */
	private $ingredients = [];

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $label;

	public function __construct(Recipe $recipe = null, $label = null) {
		$this->recipe = $recipe;
		$this->label = $label;
		$this->ingredients = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getLabel(): ?string {
		return $this->label;
	}

	public function setLabel(?string $label): self {
		$this->label = $label ?? '';
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecipe() {
		return $this->recipe;
	}

	/**
	 * @param mixed $recipe
	 */
	public function setRecipe($recipe): void {
		$this->recipe = $recipe;
	}

	/**
	 * @return Ingredient[]|Collection
	 */
	public function getIngredients() {
		return $this->ingredients;
	}

	/**
	 * @param Ingredient[]|Collection $ingredients
	 */
	public function setIngredients($ingredients): void {
		$this->ingredients = $ingredients;
	}

	public function addIngredient(Ingredient $ingredient) {
		$this->ingredients->add($ingredient);
		// set the association correctly on the owning side
		$ingredient->setGroup($this);
	}

	public function removeIngredient(Ingredient $ingredient) {
		$this->ingredients->removeElement($ingredient);
	}

}
