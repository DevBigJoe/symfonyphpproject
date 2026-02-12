<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]

final readonly class HomeController
{
    public function __construct(private Environment $twig)
    {
    }
    
    #[Route(path: "/", name: "home")]
    public function index(): Response {
        return new Response($this->twig->render("@default/HomeTemplate.html.twig"));
    }
}
