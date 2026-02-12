<?php

/*
Fuer das Aussehen verantwortlich
*/

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[AsController]
#[Route('/member')]
final readonly class MemberAreaController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    #[Route('/', name: 'member_dashboard')]
    public function dashboard(): Response
    {
        return new Response($this->twig->render('member/dashboard.html.twig'));
    }

    /*
    #[Route('/training', name: 'member_training')]
    #[IsGranted('ROLE_TRAINER', message: 'Nur Trainer haben Zugriff auf diesen Bereich.')]
    public function training(): Response
    {
        return new Response($this->twig->render('member/training.html.twig'));
    }

    */

    #[Route('/board', name: 'member_board')]
    #[IsGranted('ROLE_BOARD', message: 'Nur Vorstandsmitglieder haben Zugriff auf diesen Bereich.')]
    public function board(): Response
    {
        return new Response($this->twig->render('member/board.html.twig'));
    }
}