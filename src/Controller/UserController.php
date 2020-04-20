<?php


namespace App\Controller;


use App\Entity\ChangePassword;
use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\EditProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController {

	/**
	 * @Route("/users/editProfile", name="editProfile")
	 */
	public function editProfileAction(Request $request) {
		/** @var User $user */
		$user = $this->getUser();
		$form = $this->createForm(EditProfileType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->flush();
			$this->addFlash('success', 'Dein Profil wurde erfolgreich gespeichert!');
			return $this->redirectToRoute('recipeIndex');
		}

		return $this->render('genericForm.html.twig', array(
			'form' => $form->createView(),
			'title' => 'Profil bearbeiten',
		));
	}

	/**
	 * @Route("/users/changePassword", name="changePassword")
	 */
	public function changePasswordAction(Request $request, UserPasswordEncoderInterface $encoder) {
		$changePasswordModel = new ChangePassword();
		/** @var Form $form */
		$form = $this->createForm(ChangePasswordType::class, $changePasswordModel);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			/** @var User $user */
			$user = $this->getUser();
			$encoded = $encoder->encodePassword($this->getUser(), $changePasswordModel->getNewPassword());
			$user->setPassword($encoded);
			$this->getDoctrine()->getManager()->flush();
			$this->addFlash('success', 'Dein Passwort wurde erfolgreich geändert!');
			return $this->redirectToRoute('recipeIndex');
		}

		return $this->render('genericForm.html.twig', array(
			'form' => $form->createView(),
			'title' => 'Passwort ändern',
		));
	}
}