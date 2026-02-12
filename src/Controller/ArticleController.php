<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType as ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\CourseRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\PublishTopicMessage;

#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/articles', name: 'articles_')]
final readonly class ArticleController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private CourseRepository $courseRepository,
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private Security $security,
        private MessageBusInterface $messageBus,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {}

    #[Route('/new/{course_slug}', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, string $course_slug): Response
    {
        $course = $this->courseRepository->findOneBySlug($course_slug);
        if (!$course) {
            throw new NotFoundHttpException("Course not found");
        }
        $article = new Article();
        $article->setCourse($course);
        $form = $this->formFactory->create(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('uploadFile')->getData();

            if ($uploadedFile !== null) {
                $safeName = preg_replace(
                    '/[^a-zA-Z0-9_-]/',
                    '_',
                    pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)
                );

                $randomBytes = random_bytes(16);
                $randomHex = bin2hex($randomBytes);
                $newFilename = $safeName . '-' . $randomHex . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move(
                    $this->projectDir . '/public/uploads/articles',
                    $newFilename
                );

                $article->setUploadFilename($newFilename);
            }

            $user = $this->security->getUser();
            if ($user instanceof User) {
                $article->setAuthor($user);
            }

            if ($article->isPublished()) {
                $article->publish();
            }

            $this->em->persist($article);
            $this->em->flush();

            // Email an Kursabonnenten senden
            $this->messageBus->dispatch(
                new PublishTopicMessage($course->getSlug(), 'Neuer Artikel im Kurs: ' . $course->getName() . ': ' . $article->getTitle(), "Ein neuer Artikel wurde im Kurs " . $course->getName() . " veröffentlicht.")
            );

            // Email an Studiengangsabonnenten senden
            $degree = $course->getDegree();
            $this->messageBus->dispatch(
                new PublishTopicMessage($degree->getSlug(), 'Neuer Artikel im Kurs: ' . $course->getName() . ': ' . $article->getTitle(), "Ein neuer Artikel wurde im Kurs " . $course->getName() . " veröffentlicht.")
            );

            return new RedirectResponse(
                $this->urlGenerator->generate('articles_show', [
                    'slug' => $article->getSlug(),
                ])
            );
        }

        return new Response(
            $this->twig->render('article/new.html.twig', [
                'form' => $form->createView(),
                'isEditing' => false,
                'cancel_path' => $this->urlGenerator->generate('courses_show', [
                    'slug' => $course->getSlug(),
                ]),
            ])
        );
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Article $article): Response
    {

        $course = $article->getCourse();
        return new Response(
            $this->twig->render('article/show.html.twig', [
                'article' => $article,
                'cancel_path' => $this->urlGenerator->generate('courses_show', [
                    'slug' => $course->getSlug(),
                ]),
            ])
        );
    }

    public function recentArticles(int $max = 6): Response
    {
        return new Response(
            $this->twig->render('article/_recent.html.twig', [
                'articles' => $this->articleRepository->findRecentArticles($max),
            ])
        );
    }

}
