<?php

namespace App\Controller;

use App\Entity\ChangePassword;
use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\EditProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/users/editProfile', name: 'editProfile')]
    public function editProfileAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Dein Profil wurde erfolgreich gespeichert!');

            return $this->redirectToRoute('recipeIndex');
        }

        return $this->render('genericForm.html.twig', [
            'form' => $form->createView(),
            'title' => 'Profil bearbeiten',
        ]);
    }

    #[Route('/users/changePassword', name: 'changePassword')]
    public function changePasswordAction(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $changePasswordModel = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $changePasswordModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $encoded = $passwordHasher->hashPassword($user, $changePasswordModel->getNewPassword());
            $user->setPassword($encoded);
            $this->entityManager->flush();
            $this->addFlash('success', 'Dein Passwort wurde erfolgreich geändert!');

            return $this->redirectToRoute('recipeIndex');
        }

        return $this->render('genericForm.html.twig', [
            'form' => $form->createView(),
            'title' => 'Passwort ändern',
        ]);
    }
}
