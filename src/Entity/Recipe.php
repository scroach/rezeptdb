<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeRepository")
 * @ORM\Table(name="recipes")
 */
class Recipe {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=100)
	 * @Assert\NotBlank()
	 */
	private $label;

	/**
	 * @ORM\Column(type="text")
	 * @Assert\NotBlank()
	 */
	private $description;

	/**
	 * @ORM\Column(type="integer")
	 * @Assert\NotBlank()
	 */
	private $effort;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $originUrl;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Tag")
	 * @ORM\JoinTable(name="recipes_tags")
	 */
	private $tags;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Ingredient", mappedBy="recipe")
	 */
	private $ingredients;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="recipe")
	 */
	private $images;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $created;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $modified;

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

	public function getOriginUrl(): ?string {
		return $this->originUrl;
	}

	public function setOriginUrl(?string $originUrl): self {
		$this->originUrl = $originUrl;

		return $this;
	}

	public function getCreated(): ?\DateTimeInterface {
		return $this->created;
	}

	public function setCreated(\DateTimeInterface $created): self {
		$this->created = $created;

		return $this;
	}

	public function getModified(): ?\DateTimeInterface {
		return $this->modified;
	}

	public function setModified(\DateTimeInterface $modified): self {
		$this->modified = $modified;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	public function getDescriptionParagraphs() {
		return explode("\r\n", $this->description);
	}

	/**
	 * @param mixed $description
	 */
	public function setDescription($description): void {
		$this->description = $description;
	}

	/**
	 * @return mixed
	 */
	public function getEffort() {
		return $this->effort;
	}

	/**
	 * @param mixed $effort
	 */
	public function setEffort($effort): void {
		$this->effort = $effort;
	}

	/**
	 * @return mixed
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * @param mixed $tags
	 */
	public function setTags($tags): void {
		$this->tags = $tags;
	}

	/**
	 * @return mixed
	 */
	public function getIngredients() {
		return $this->ingredients;
	}

	/**
	 * @param mixed $ingredients
	 */
	public function setIngredients($ingredients): void {
		$this->ingredients = $ingredients;
	}

	/**
	 * @return Image[]
	 */
	public function getImages() {
		return $this->images;
	}

	/**
	 * @param mixed $images
	 */
	public function setImages($images): void {
		$this->images = $images;
	}


}
