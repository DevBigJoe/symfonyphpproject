<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class DemoController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    #[Route('/demo', name: 'app_demo')]
    public function index(): Response
    {
        return new Response($this->twig->render('@demo/demo.html.twig'));
    }
}
