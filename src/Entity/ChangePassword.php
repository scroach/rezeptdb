<?php

namespace App\Entity;


use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword {

	/**
	 * @SecurityAssert\UserPassword(
	 *     message = "Da hast du dich scheinbar vertippt, das aktuelle Passwort stimmt nicht!"
	 * )
	 */
	protected $oldPassword;

	/**
	 * @Assert\Length(
	 *     min = 6,
	 *     minMessage = "Dein neues Passwort muss mindestens 6 Zeichen lang sein!"
	 * )
	 */
	protected $newPassword;

	/**
	 * @return mixed
	 */
	public function getOldPassword() {
		return $this->oldPassword;
	}

	/**
	 * @param mixed $oldPassword
	 */
	public function setOldPassword($oldPassword): void {
		$this->oldPassword = $oldPassword;
	}

	/**
	 * @return mixed
	 */
	public function getNewPassword() {
		return $this->newPassword;
	}

	/**
	 * @param mixed $newPassword
	 */
	public function setNewPassword($newPassword): void {
		$this->newPassword = $newPassword;
	}


}