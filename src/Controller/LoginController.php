<?php


namespace App\Controller;

use App\Entity\User;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Runner\Exception;
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
	public function register() {
		return $this->render('security/register.html.twig', array('error' => null));
	}

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/addUser", name="addUser")
     */
	public function addUser(Request $request, UserPasswordEncoderInterface $encoder) {
	    $email = $request->get('_email');
        $username = $request->get('_username');
        $password1 = $request->get('_password1');
        $password2 = $request->get('_password2');
        $error = $this->checkRequest($email, $username, $password1, $password2);
        if($error)
            return $this->render('security/register.html.twig', array('error' => $error));
        $user = $this->buildUser($encoder, $username, $password1, $email);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();
	    return $this->redirectToRoute('login');
    }

    /**
     * @param String $email
     * @param String $username
     * @param String $password1
     * @param String $password2
     * @return String|null
     */
    private function checkRequest(String $email, String $username, String $password1, String $password2) {
        if(empty(trim($email)) || empty(trim($username)) || empty(trim($password1)) || empty(trim($password2)))
            return "Bitte alle Felder ausfüllen!";
        if($password1 !== $password2)
            return "Passwörter stimmen nicht überein!";
        if($this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => $username]))
            return "Nutzername schon vergeben!";
        return null;
    }

    /**
     * @param UserPasswordEncoderInterface $encoder
     * @param $username
     * @param $password
     * @param $email
     * @return User
     */
    protected function buildUser(UserPasswordEncoderInterface $encoder, $username, $password, $email): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setEmail($email);
        return $user;
    }

}