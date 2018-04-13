<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ingredients")
 */
class Ingredient {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Recipe")
	 */
	private $recipe;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $amount;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $label;

	public function getId() {
		return $this->id;
	}

	public function getLabel(): ?string {
		return $this->label;
	}

	public function setLabel(string $label): self {
		$this->label = $label;

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
	 * @return mixed
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @param mixed $amount
	 */
	public function setAmount($amount): void {
		$this->amount = $amount;
	}

}
