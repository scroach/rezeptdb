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
	 * @var IngredientGroup
	 * @ORM\ManyToOne(targetEntity="App\Entity\IngredientGroup", inversedBy="ingredients")
	 */
	private $group;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $amount;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $label;

	public function __construct(IngredientGroup $group = null, $label = null, $amount = null) {
		$this->group = $group;
		$this->amount = $amount;
		$this->label = $label;
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

	public function setLabel(string $label): self {
		$this->label = $label;

		return $this;
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

	/**
	 * @return IngredientGroup
	 */
	public function getGroup(): IngredientGroup {
		return $this->group;
	}

	/**
	 * @param IngredientGroup $group
	 */
	public function setGroup(IngredientGroup $group): void {
		$this->group = $group;
	}

}
