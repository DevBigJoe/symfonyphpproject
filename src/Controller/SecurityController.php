<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class SecurityController
{

    public function __construct (
        private Environment $twig,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator
    ) 
    {    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->security->getUser() !== null) {
            return new RedirectResponse($this->urlGenerator->generate("home"));
        } 
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return new Response($this->twig->render('@default/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]));
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        //logout user
        $this->security->logout();

        //Disable csrf logout
        $this->security->logout(false);


        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
