<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType as ArticleFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Filesystem\Filesystem;



#[AsController]
#[IsGranted(attribute: 'ROLE_USER')]
#[Route('/overview', name: 'overview_')]
final readonly class ArticleOverviewController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
        private Security $security,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /**
     * @return Article[]
     */
    private function getAccessibleArticles(): array
    {
        $user = $this->security->getUser();

        if ($user === null) {
            throw new \RuntimeException('No user logged in');
        }

        return $this->security->isGranted('ROLE_ADMIN')
            ? $this->articleRepository->findAll()
            : $this->articleRepository->findBy(['author' => $user]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request): Response
    {
        $user = $this->security->getUser();

        if (
            !$this->security->isGranted('ROLE_ADMIN') 
            && $article->getAuthor() !== $user
        ) {
            throw new AccessDeniedException('You cannot edit this article.');
        }

        $currentFilename = $article->getUploadFile();

        $form = $this->formFactory->create(ArticleFormType::class, $article);
        $form->handleRequest($request);

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $form->get('uploadFile')->getData();

        if ($uploadedFile !== null) {
            // Falls es schon eine Datei gab → alte löschen
            if ($currentFilename) {
                $oldPath = $this->projectDir . '/public/uploads/articles/' . $currentFilename;
                if ($this->filesystem->exists($oldPath)) {
                    $this->filesystem->remove($oldPath);
                }
            }

            $safeName = preg_replace(
                '/[^a-zA-Z0-9_-]/',
                '_',
                pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)
            );

            $newFilename = $safeName . '-' . bin2hex(random_bytes(16)) . '.' . $uploadedFile->guessExtension();

            $uploadedFile->move(
                $this->projectDir . '/public/uploads/articles',
                $newFilename
            );

            $article->setUploadFilename($newFilename);
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUpdatedAt();

            $this->em->persist($article);
            $this->em->flush();

            $articles = $this->getAccessibleArticles();

            return new Response($this->twig->render(
                'article_overview/article_overview.html.twig',
                [
                'articles' => $articles,
            ],
            ));
        }

        return new Response(
            $this->twig->render('article/new.html.twig', [
                'form' => $form->createView(),
                'article' => $article,
                'isEditing' => true,
                'cancel_path' => $this->urlGenerator->generate('overview_overview'),
            ])
        );
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Article $article, Request $request): RedirectResponse
    {
        $user = $this->security->getUser();
        $submittedToken = $request->request->get('_token');

        if (!$user) {
            throw new \RuntimeException('No user logged in');
        }

        if (!$this->security->isGranted('ROLE_ADMIN') && $article->getAuthor() !== $user) {
            throw new AccessDeniedException('You cannot delete this article.');
        }

        $tokenValue = $submittedToken === null || $submittedToken === '' ? null : (string) $submittedToken;
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('delete-article'.$article->getId(), $tokenValue))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        if ($article->getUploadFilename()) {
            $file = $this->projectDir . '/public/uploads/articles/' . $article->getUploadFilename();
            if ($this->filesystem->exists($file)) {
                $this->filesystem->remove($file);
            }
        }

        $this->em->remove($article);
        $this->em->flush();

        return new RedirectResponse($this->urlGenerator->generate('overview_overview'));
    }

    #[Route('/', name: 'overview', methods: ['GET'])]
    public function index(): Response
    {
        $articles = $this->getAccessibleArticles();

        return new Response($this->twig->render(
            'article_overview/article_overview.html.twig',
            ['articles' => $articles],
        ));
    }
}
