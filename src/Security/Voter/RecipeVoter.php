<?php

namespace App\Security\Voter;

use App\Entity\Recipe;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class RecipeVoter extends Voter {

	const READ = 'RECIPE_READ';
	const EDIT = 'RECIPE_EDIT';

	protected function supports($attribute, $subject) {
		return in_array($attribute, [self::READ, self::EDIT]) && $subject instanceof Recipe;
	}

	/**
	 * @param string $attribute
	 * @param Recipe $subject
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
		$user = $token->getUser();
		// if the user is anonymous, do not grant access
		if (!$user instanceof UserInterface) {
			return false;
		}

		switch ($attribute) {
			case self::READ:
			case self::EDIT:
				return $user === $subject->getUser();
		}

		return false;
	}
}
