<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;
use Dompdf\Dompdf;
use App\Entity\Article;

#[AsController]
#[IsGranted('ROLE_USER')]
final readonly class HealthController
{
    #[Route('/health', name: 'health_check')]
    public function checkHealth(
        EntityManagerInterface $em,
        UserRepository $userRepo,
        ArticleRepository $articleRepo
    ): JsonResponse {
        $status = 'ok';
        $data = [];

        try {
            // --- Datenbank prüfen ---
            $em->getConnection()->executeQuery('SELECT 1')->fetchOne();
            $data['database'] = 'ok';

            // --- Gesamtzahlen ---
            $data['users'] = $userRepo->count([]);
            $data['articles'] = $articleRepo->count([]);

            // --- Aktive User im letzten Jahr ---
            $since = new \DateTimeImmutable('2025-10-01 00:00:00');
            $data['activeUsers'] = (int) $userRepo->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.lastLogin IS NOT NULL')
                ->andWhere('u.lastLogin >= :since')
                ->setParameter('since', $since)
                ->getQuery()
                ->getSingleScalarResult();

            // --- Seitenstatus prüfen ---
            $data['loginPage'] = $this->checkUrlStatus('/login') ? 'ok' : 'error';

        } catch (\Throwable $e) {
            $status = 'error';
            $data['message'] = $e->getMessage();
            $data['database'] = 'error';
            $data['loginPage'] = 'error';
            $data['activeUsers'] = 0;
        }

        return new JsonResponse(
            array_merge(['status' => $status], $data),
            $status === 'ok' ? 200 : 500
        );
    }

    #[Route('/health/dashboard', name: 'health_dashboard')]
    public function dashboard(Environment $twig): Response
    {
        $html = $twig->render('health/dashboard.html.twig');
        return new Response($html);
    }

    //Datadumper für API´s, falls jemand sich alle Bloseinträge laden will
    #[Route('/health/datadump', name: 'health_datadump')]
    public function datadump(
        ArticleRepository $articleRepo
    ): JsonResponse {
        $status = 'ok';
        $data = [];

        try {
            // Alle articleseinträge abrufen
            $articles = $articleRepo->findAll();

            // Array für JSON erstellen, Autor als Unterobjekt
            $data['articles'] = array_map(function($article) {
                $author = $article->getAuthor();
                return [
                    'id' => $article->getId(),
                    'title' => $article->getTitle(),
                    'content' => $article->getContent(),
                    'createdAt' => $article->getCreatedAt()->format(\DateTime::ATOM),
                    'updatedAt' => $article->getUpdatedAt()->format(\DateTime::ATOM),
                    'author' => [
                        'id' => $author->getId(),
                        'name' => $author->getName(),
                        'email' => $author->getEmail(),
                    ],
                ];
            }, $articles);

        } catch (\Throwable $e) {
            $status = 'error';
            $data['message'] = $e->getMessage();
            $data['articles'] = [];
        }

        // JSON pretty print erzeugen
        $json = json_encode(array_merge(['status' => $status], $data), JSON_PRETTY_PRINT);

        // JsonResponse korrekt zurückgeben
        return new JsonResponse($json, $status === 'ok' ? 200 : 500, [], true);
    }

    //-----------------------------------PDF-ROute------------------------------------------
    #[Route('/health/datadump/{id}/pdf', name: 'health_print_pdf', methods: ['GET'])]
        public function printPdf(
            Article $article
        ): Response {
            $html = "
                <h1>{$article->getTitle()}</h1>
                <hr>
                <p>{$article->getContent()}</p>
            ";

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            $filename = 'article_' . $article->getId() . '.pdf';

            return new Response(
                $output,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                ]
            );
        }

    /**
     * Prüft, ob eine URL innerhalb der App erreichbar ist
     */
    private function checkUrlStatus(string $path): bool
    {
        $baseUrl = $_SERVER['APP_URL'] ?? 'http://localhost'; // Basis-URL der App
        $url = rtrim($baseUrl, '/') . $path;

        try {
            $headers = @get_headers($url);
            return $headers && strpos($headers[0], '200') !== false;
        } catch (\Throwable) {
            return false;
        }
    }
}
