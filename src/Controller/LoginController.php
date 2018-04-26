<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends Controller {

	/**
	 * @Route("/login", name="login")
	 */
	public function login(Request $request, AuthenticationUtils $authenticationUtils) {
		$error = $authenticationUtils->getLastAuthenticationError();
		$lastUsername = $authenticationUtils->getLastUsername();
		return $this->render('security/login.html.twig', array(
			'last_username' => $lastUsername,
			'error' => $error,
		));
	}

	/**
	 * @Route("/register", name="register")
	 */
	public function register(UserPasswordEncoderInterface $encoder) {
		$user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => 'admin']) ?? new User();
		$user->setUsername('admin');
		$user->setPassword($encoder->encodePassword($user, 'admin'));
		$user->setEmail('admin@admin.com');
		$this->getDoctrine()->getManager()->persist($user);
		$this->getDoctrine()->getManager()->flush();
	}

}