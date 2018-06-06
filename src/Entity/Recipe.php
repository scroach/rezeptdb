<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecipeRepository")
 * @ORM\Table(name="recipes")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
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
	 * @ORM\OneToMany(targetEntity="App\Entity\Ingredient", mappedBy="recipe", cascade={"persist"}, orphanRemoval=true)
	 */
	private $ingredients = [];

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

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $deletedAt;

	/**
	 * Property for file uploads
	 * @var null|UploadedFile[]
	 */
	private $file = null;
	private $tagsString;

	private $searchRating = 0;

	/**
	 * Recipe constructor.
	 */
	public function __construct() {
		$this->tags = new ArrayCollection();
		$this->ingredients = new ArrayCollection();
		$this->images = new ArrayCollection();
	}

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
	 * @return Tag[]|ArrayCollection
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

	public function getTagsString() {
		if ($this->tagsString) {
			return $this->tagsString;
		}

		return implode(',', array_map(function (Tag $tag) {
			return $tag->getLabel();
		}, $this->tags->toArray()));
	}

	public function setTagsString(string $tags) {
		return $this->tagsString = $tags;
	}

	/**
	 * @return Ingredient[]|ArrayCollection
	 */
	public function getIngredients() {
		return $this->ingredients;
	}

	public function getIngredientsText() {
		$result = [];
		foreach ($this->getIngredients() as $ingredient) {
			$result[] = $ingredient->getAmount().' '.$ingredient->getLabel();
		}
		return $result;
	}

	public function setIngredientsText($ingredientsList) {
		$this->ingredients->clear();
		foreach ($ingredientsList as $ingredient) {
			if ($ingredient) {
				$this->ingredients->add(new Ingredient($this, $ingredient));
			}
		}
	}

	public function addIngredient(Ingredient $ingredient) {
		$this->ingredients->add($ingredient);
	}

	public function removeIngredient(Ingredient $ingredient) {
		$this->ingredients->removeElement($ingredient);
	}

	/**
	 * @param mixed $ingredients
	 */
	public function setIngredients($ingredients): void {
		$this->ingredients = $ingredients;
	}

	/**
	 * @return Image[]|ArrayCollection
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

	public function addImage(Image $image) {
		$this->images->add($image);
	}


	public function getFiles() {
		return $this->file;
	}

	public function setFiles($images) {
		$this->file = $images;
	}

	/**
	 * @return float
	 */
	public function getSearchRating(): float {
		return $this->searchRating;
	}

	/**
	 * @param float $searchRating
	 */
	public function setSearchRating(float $searchRating): void {
		$this->searchRating = $searchRating;
	}

	/**
	 * @return mixed
	 */
	public function getDeletedAt() {
		return $this->deletedAt;
	}

	/**
	 * @param mixed $deletedAt
	 */
	public function setDeletedAt($deletedAt): void {
		$this->deletedAt = $deletedAt;
	}

}
