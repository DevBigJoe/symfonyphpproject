<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Symfony\Bundle\SecurityBundle\Security;

#[AsController]
final readonly class UserProfile
{
    public function __construct(private Environment $twig) {

    }
    
    #[Route(path: "/profile", name: "profile")]
    public function loadProfile(Security $security): Response {
        return new Response(
            $this->twig->render('@default/UserProfile.html.twig', [
                'user' => $security->getUser(),
            ])
        );
    }
}
