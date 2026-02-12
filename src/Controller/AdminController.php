<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/admin', name: 'admin_')]
final readonly class AdminController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    #[Route('/users/{id}', name: 'show', methods: ['GET'])]
    public function showUserInfo(
        User $user,
    ): Response {
        return new Response($this->twig->render(
            'user/show.html.twig',
            ['user' => $user],
        ));
    }

    #[Route('/users', name: 'users')]
    public function users(UserRepository $userRepository) : Response {
        return new Response($this->twig->render(
            'user/list.html.twig',
            ['users' => $userRepository->findAll()]
        ));
    }
}