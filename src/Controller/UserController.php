<?php declare(strict_types=1);

// src/Controller/RegistrationController.php
namespace App\Controller;

// ...
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Twig\Environment;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Email;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class UserController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private TokenGeneratorInterface $tokenGenerator,
        private Environment $twig,
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/register', 'registration', methods: ["GET", "POST"])]
    public function registration(Request $request): Response
    {
        // ... e.g. get the user data from a registration form
        $user = new User("");
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
            $token = $this->tokenGenerator->generateToken();
            $user->setVerificationToken($token);
            $expiry = new DateTimeImmutable();
            $expiry = $expiry->modify("+1 day");
            $user->setVerificationTokenExpiresAt($expiry);
            $this->em->persist($user);
            $this->em->flush();
            $email = new Email()
                ->from('noreply@example.com')
                ->to($user->getEmail())
                ->subject('Please verify your email')
                ->html('<p>Click <a href="http://localhost:8090/verify/' . $user->getVerificationToken() . '">here</a> to verify your email.</p>');
            $this->mailer->send($email);

            return new Response($this->twig->render(
                'emailSent.html.twig',
                ['email' => $user->getEmail()],
            ));
        }
        return new Response($this->twig->render(
            '@default/register.html.twig',
            [
                'form' => $form->createView(),
            ],
        ));
    }

    #[Route('/verify/{token}', name: 'verify_email', methods: ["GET"])]
    public function verifyEmail(string $token) : Response
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['verificationToken' => $token]);
        if (!$user) {
            return new Response($this->twig->render(
                'error.html.twig',
                ['error' => 'Ungültiger Verifizierungs-Token.'],
            ));
        }
        if ($user->getVerificationTokenExpiresAt() < new DateTimeImmutable()) {
            return new Response($this->twig->render(
                'error.html.twig',
                ['error' => 'Der Verifizierungs-Token ist abgelaufen.'],
            ));
        }
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setVerificationTokenExpiresAt(null);
        $this->em->persist($user);
        $this->em->flush();
        return new Response($this->twig->render(
            '@default/verificationSuccess.html.twig',
        ));
    }
}
