<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User implements UserInterface, \Serializable {
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=25, unique=true)
	 * @Assert\Length(min="3", max="50")
	 */
	private $username;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=64)
	 */
	private $password;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=254, unique=true)
	 * @Assert\Email(mode="strict")
	 */
	private $email;

	/**
	 * @var boolean
	 * @ORM\Column(name="is_active", type="boolean")
	 */
	private $isActive;

	public function __construct() {
		$this->isActive = true;
	}

	public function getSalt() {
		// we use bcrypt so we don't need a salt
		return null;
	}

	public function getRoles() {
		return array('ROLE_USER');
	}

	public function eraseCredentials() {
	}

	public function serialize() {
		return serialize(array(
			$this->id,
			$this->username,
			$this->password,
		));
	}

	public function unserialize($serialized) {
		list (
			$this->id,
			$this->username,
			$this->password,
			) = unserialize($serialized, ['allowed_classes' => false]);
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id): void {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getUsername(): string {
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername(string $username): void {
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string {
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword(string $password): void {
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string {
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail(string $email): void {
		$this->email = $email;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->isActive;
	}

	/**
	 * @param bool $isActive
	 */
	public function setIsActive(bool $isActive): void {
		$this->isActive = $isActive;
	}


}