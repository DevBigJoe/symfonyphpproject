<?php declare(strict_types=1);
# Neues Passwort wird nicht richtig in Datenbank gespeichert aber geändert (passwort reset bundel recherchieren)
# bei profile/edit Button zu profile/editPassword hinzufügen
# profile/editPassword twig bearbeiten

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Entity\User;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use DateTimeImmutable;

#[AsController]
#[Route('/profile/editPassword', name: 'profile_change_password')]
class ChangePasswordController
{
    public function __invoke(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        TokenGeneratorInterface $tokenGenerator,
        Environment $twig,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        // Formular erstellen
        $form = $formFactory->create(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Neues Passwort aus dem Formular holen
            $newPassword = $form->get('password')->getData();

            // Neues Passwort hashen und setzen
            $user->setPassword(
                $passwordHasher->hashPassword($user, $newPassword)
            );

            // Token erzeugen,
            $token = $tokenGenerator->generateToken();

            // Token in der DB speichern
            $user->setVerificationToken($token);

            //Ablaufdatum setzen, z.B. 24h
            $expiry = new DateTimeImmutable('+1 day');
            $user->setVerificationTokenExpiresAt($expiry);

            // Änderungen speichern
            $em->flush();
        }

        return new Response($twig->render('@default/changePassword.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
