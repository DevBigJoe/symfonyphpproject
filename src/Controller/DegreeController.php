<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Degree;
use App\Form\DegreeType;
use App\Repository\DegreeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\User;
use App\Repository\CourseRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Message\SubscribeToTopicMessage;
use App\Repository\SubscriptionRepository;
use App\Message\UnsubscribeFromTopicMessage;

#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/degrees', name: 'degrees_')]
final readonly class DegreeController
{
    public function __construct(
        private DegreeRepository $degreeRepository,
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private CourseRepository $courseRepository,
        private MessageBusInterface $messageBus,
        private Security $security,
    ) {
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REDAKTION')]
    public function new(Request $request): Response
    {
        $degree = new Degree();
        $form   = $this->formFactory->create(DegreeType::class, $degree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $degree->initSlug();
            $existing = $this->degreeRepository->findOneBySlug($degree->getSlug());
            if ($existing !== null) {
                $form->get('name')->addError(new FormError(
                    $this->translator->trans('degree.unique', [], 'validators')
                ));
            } else {
                $this->em->persist($degree);
                $this->em->flush();
                return new RedirectResponse($this->urlGenerator->generate('degrees_list'));
            }
        }

        return new Response($this->twig->render(
            'degree/new.html.twig',
            [
                'form' => $form->createView(),
            ],
        ));
    }

    #[Route('/', name: 'list')]
    public function list(): Response
    {
        $degrees = $this->degreeRepository->findAll();

        return new Response($this->twig->render(
            'degree/list.html.twig',
            ['degrees' => $degrees],
        ));
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug, Security $security, SubscriptionRepository $subscriptionRepository): Response
    {
        $degree = $this->degreeRepository->findOneBySlug($slug);
        if (!$degree) {
            throw new NotFoundHttpException("Degree not found");
        }

        $user = $security->getUser();
        $isSubscribed = false;

        if ($user instanceof User) {
            $isSubscribed = $subscriptionRepository->isUserSubscribed($user, $degree->getSlug());
        }    


        $courses = $this->courseRepository->findByDegreeSlug($slug);

        return new Response($this->twig->render(
            'degree/show.html.twig',
            [
                'courses' => $courses, 'degree' => $degree,
                'isSubscribed' => $isSubscribed,
            ],
        ));
    }

    #[Route('/subscribe/{slug}', name: 'subscribe', methods: ['POST'])]
    public function sub(string $slug, SubscriptionRepository $subscriptionRepository): Response
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
         if (!$user) {
            throw new AccessDeniedException('Du musst eingeloggt sein, um ein Thema zu abonnieren.');
        }
        $userId = $user->getId();
        if (!$userId) {
            throw new \LogicException("User has no ID");
        }
        $topic = $slug;
         $existing = $subscriptionRepository->findOneBy([
            'user' => $user,
            'topic' => $topic,
        ]);
        if ($existing) {
            $this->em->remove($existing);
            $this->em->flush();
            $this->messageBus->dispatch(
                new UnsubscribeFromTopicMessage($userId, $topic)
            );
        } else {
            $this->messageBus->dispatch(
                new SubscribeToTopicMessage($userId, $topic)
            );
        }
        // Erfolgreichem Dispatch 
        return new RedirectResponse($this->urlGenerator->generate('degrees_show', ['slug' => $slug]));
    }
}