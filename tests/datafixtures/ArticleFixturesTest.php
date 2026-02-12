<?php declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\DataFixtures\ArticleFixtures;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\CourseFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class ArticleFixturesTest extends KernelTestCase
{
    private ArticleFixtures $fixtures;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        // Fixtures Loader
        $loader = new Loader();

        $loader->addFixture(new UserFixtures());
        $loader->addFixture(new CourseFixtures());

        // ArticleFixtures setzen
        $this->fixtures = new ArticleFixtures($kernel);
        $loader->addFixture($this->fixtures);

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);

        // Alle Fixtures laden
        $executor->execute($loader->getFixtures());
    }

    public function testLoadArticles(): void
    {
        // Wir prüfen, dass die Fixtures geladen werden können
        //$this->fixtures->load($this->em); Wurde schon in setup gemacht

        // Alle Artikel aus der DB holen
        $articles = $this->em->getRepository(Article::class)->findAll();
        self::assertNotEmpty($articles, 'Es wurden keine Artikel geladen');

        foreach ($articles as $article) {
            // Titel und Content prüfen
            self::assertNotEmpty($article->getTitle(), 'Artikel hat keinen Titel');
            self::assertNotEmpty($article->getContent(), 'Artikel hat keinen Inhalt');

            // PublishedAt prüfen
            self::assertNotNull($article->getPublishedAt(), 'Artikel ist nicht veröffentlicht');
        }
    }

    public function testArticleHasValidArticleType(): void
    {
        $articles = $this->em->getRepository(Article::class)->findAll();
        self::assertNotEmpty($articles, 'Keine Artikel gefunden');

        foreach ($articles as $article) {
            // Prüfen, dass ArticleType gesetzt ist
            self::assertNotNull(
                $article->getArticleType(),
                sprintf('Artikel "%s" hat keinen ArticleType gesetzt', $article->getTitle())
            );

            // Prüfen, dass ArticleType ein gültiger Enum-Wert ist
            self::assertContains(
                $article->getArticleType()->value,
                array_column(\App\Entity\Enum\ArticleType::cases(), 'value'),
                sprintf('Artikel "%s" hat einen ungültigen ArticleType', $article->getTitle())
            );
        }
    }

    public function testPublishedAtIsAfterCreatedAt(): void
    {
        $articles = $this->em->getRepository(Article::class)->findAll();
        self::assertNotEmpty($articles, 'Keine Artikel gefunden');

        foreach ($articles as $article) {
            $createdAt = $article->getCreatedAt();
            $publishedAt = $article->getPublishedAt();

            self::assertNotNull($publishedAt, sprintf('Artikel "%s" hat kein publishedAt', $article->getTitle()));

            self::assertGreaterThanOrEqual(
                $createdAt,
                $publishedAt,
                sprintf(
                    'Artikel "%s": publishedAt (%s) liegt vor createdAt (%s)',
                    $article->getTitle(),
                    $publishedAt->format('Y-m-d H:i:s'),
                    $createdAt->format('Y-m-d H:i:s')
                )
            );
        }
    }



}
