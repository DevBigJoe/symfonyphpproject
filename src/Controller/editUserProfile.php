<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;


#[AsController]
#[Route('/profile/edit', name: 'profile_edit')]
final readonly class editUserProfile
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private Environment $twig
    ) {}

    public function __invoke(Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            return new Response('Unauthorized', 401);
        }

        // Formular erzeugen
        $form = $this->formFactory->create(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Wenn Entity existiert nur flush
            $this->em->flush();

            return new RedirectResponse('/profile');
        }

        return new Response(
            $this->twig->render('@default/editUserProfile.html.twig', [
                'form' => $form->createView(),
            ])
        );
    }
}
