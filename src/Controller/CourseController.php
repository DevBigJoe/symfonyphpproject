<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\DegreeRepository;
use App\Repository\SubscriptionRepository;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Course;
use App\Form\CourseType;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\CourseRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Message\SubscribeToTopicMessage;
use App\Message\UnsubscribeFromTopicMessage;
use App\Entity\User;


#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/courses', name: 'courses_')]
final readonly class CourseController 
{
    public function __construct(
        private DegreeRepository $degreeRepository,
        private CourseRepository $courseRepository,
        private ArticleRepository $articleRepository,
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private MessageBusInterface $messageBus,
        private Security $security,
    ) {
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug, Security $security, SubscriptionRepository $subscriptionRepository): Response
    {

        $course = $this->courseRepository->findOneBySlug($slug);
        if (!$course) {
            throw new NotFoundHttpException("Course not found");
        }

        $user = $security->getUser();
        $isSubscribed = false;

        if ($user instanceof User) {
            $isSubscribed = $subscriptionRepository->isUserSubscribed($user, $course->getSlug());
        }
 
        return new Response($this->twig->render(
            'course/show.html.twig',
            [
                'course' => $course, 
                'articles' => $this->articleRepository->findPublishedArticlesForCourse($course),
                'isSubscribed' => $isSubscribed,
            ],
        ));
    }

    #[IsGranted('ROLE_REDAKTION')]
    #[Route('/{slug}/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, string $slug): Response
    {
        $degree = $this->degreeRepository->findOneBySlug($slug);
        if (!$degree) {
            throw new NotFoundHttpException("Degree not found");
        }
        $course = new Course();
        $form = $this->formFactory->create(CourseType::class, $course);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $course->initSlug();
            $course->setDegree($degree);
            $existing = $this->courseRepository->findOneBySlug($course->getSlug());
            if ($existing !== null) {
                $form->get('name')->addError(new FormError(
                    $this->translator->trans('course.unique', [], 'validators')
                ));
            } else {
                $this->em->persist($course);
                $this->em->flush();
                return new RedirectResponse($this->urlGenerator->generate('degrees_show', [
                    'slug' => $course->getDegree()->getSlug(),
                ]));
            }
        }
        return new Response($this->twig->render(
            'course/new.html.twig',
            ['form' => $form->createView(), 'degree' => $degree],
        ));
    }

   #[Route('/subscribe/{slug}', name: 'subscribe', methods: ['POST'])]
    public function subscribe(string $slug, SubscriptionRepository $subscriptionRepository): Response 
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException('Du musst eingeloggt sein.');
        }

        $topic = $slug;

        $existing = $subscriptionRepository->findOneBy([
            'user' => $user,
            'topic' => $topic,
        ]);

        $userId = $user->getId();
        if (!$userId) {
            throw new \LogicException("User has no ID");
        }

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

        return new RedirectResponse(
            $this->urlGenerator->generate('courses_show', ['slug' => $slug])
        );
    }


}