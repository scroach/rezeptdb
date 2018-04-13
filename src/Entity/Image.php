<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="images")
 */
class Image {
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
	 * @ORM\Column(type="string")
	 */
	private $url;


	public function getId() {
		return $this->id;
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
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param mixed $url
	 */
	public function setUrl($url): void {
		$this->url = $url;
	}


}
