<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'register')]
    public function register(UserPasswordHasherInterface $passwordHasher): Response
    {
        if($this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin'])) {
            return new Response('User already exists');
        }

        $user = new User();
        $user->setUsername('admin');
        $user->setPassword($passwordHasher->hashPassword($user, 'admin'));
        $user->setEmail('admin@admin.com');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response('User registered');
    }
}
