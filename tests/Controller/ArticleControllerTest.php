<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Article;
use App\Entity\Enum\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Entity\Course;
use App\Entity\Degree;

final class ArticleControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Course $course;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $em = $this->getEntityManager();
        $normalUser = new User('user@test.de')
            -> setName('User')
            -> setRoles(['ROLE_USER'])
            -> setPassword('test')
            -> setIsVerified(true);
        $em->persist($normalUser);

        // Test-Degree erstellen
        $degree = new Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);

        // Test-Course erstellen
        $course = new Course();
        $course->setName('Test Course');
        $course->setDescription('Test Description');
        $course->setDegree($degree);
        $course->initSlug();
        $em->persist($course);

        $em->flush();
        $em->clear();
        $this->client->loginUser($normalUser);
        $this->course = $course;
    }

    public function testCreateArticleSuccessfully(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Neuen Artikel erstellen');

        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'       => 'Test Article Title',
            'article[content]'     => 'This is the test article content.',
            'article[articleType]' => ArticleType::Explanation->value,
            'article[published]'   => false,
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Test Article Title');
        self::assertSelectorTextContains('.article-type', 'Erklärung');

        // Verify article was persisted
        $em      = $this->getEntityManager();
        $article = $em->getRepository(Article::class)->findOneBy(['title' => 'Test Article Title']);

        self::assertNotNull($article);
        self::assertSame('Test Article Title', $article->getTitle());
        self::assertSame('This is the test article content.', $article->getContent());
        self::assertSame('User', $article->getAuthor()->getName());
        self::assertSame(ArticleType::Explanation, $article->getArticleType());
        self::assertFalse($article->isPublished());
        self::assertNull($article->getPublishedAt());
    }

    public function testCreateArticleWithPublishedFlag(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());


        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'       => 'Published Article',
            'article[content]'     => 'Content for published article.',
            'article[articleType]' => ArticleType::ExampleExam->value,
            'article[published]'   => true,
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();

        $em      = $this->getEntityManager();
        $article = $em->getRepository(Article::class)->findOneBy(['title' => 'Published Article']);

        self::assertNotNull($article);
        self::assertTrue($article->isPublished());
        self::assertNotNull($article->getPublishedAt());
    }

    public function testCreateArticleWithMissingTitle(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());


        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'   => '',
            'article[content]' => 'Content without title.',
        ]);

        $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.invalid-feedback, .form-error');
    }

    public function testCreateArticleWithMissingContent(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());


        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'   => 'Title without content',
            'article[content]' => '',
        ]);

        $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.invalid-feedback, .form-error');
    }

    public function testCreateArticleWithAllFieldsEmpty(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());

        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'   => '',
            'article[content]' => '',
        ]);

        $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.invalid-feedback, .form-error');

        // Verify no article was created
        $em    = $this->getEntityManager();
        $count = $em->getRepository(Article::class)->count([]);
        self::assertSame(0, $count);
    }

    public function testCreateArticleWithAllArticleTypes(): void
    {
        foreach (ArticleType::cases() as $articleType) {
            $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());

            $form = $crawler->selectButton('Artikel erstellen')->form([
                'article[title]'       => 'Article for ' . $articleType->name,
                'article[content]'     => 'Content for ' . $articleType->value,
                'article[articleType]' => $articleType->value,
            ]);

            $this->client->submit($form);

            self::assertResponseRedirects();

            $em      = $this->getEntityManager();
            $article = $em->getRepository(Article::class)->findOneBy(['title' => 'Article for ' . $articleType->name]);

            self::assertNotNull($article);
            self::assertSame($articleType, $article->getArticleType());

            $em->clear();
        }
    }

    public function testCreateArticleWithoutArticleType(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());


        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'       => 'Article without type',
            'article[content]'     => 'Content without article type.',
            'article[articleType]' => '',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();

        $em      = $this->getEntityManager();
        $article = $em->getRepository(Article::class)->findOneBy(['title' => 'Article without type']);

        self::assertNotNull($article);
        self::assertNull($article->getArticleType());
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testSlugIsGeneratedFromTitle(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());

        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'   => 'Mein erster Artikel!',
            'article[content]' => 'Text ist ein text',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();

        $em = $this->getEntityManager();
        $article = $em->getRepository(Article::class)
            ->findOneBy(['title' => 'Mein erster Artikel!']);
        self::assertNotNull($article);
        self::assertSame('mein-erster-artikel', $article->getSlug());
    }

    public function testNewArticleIsUnpublishedByDefault(): void
    {
        $crawler = $this->client->request('GET', '/articles/new/' . $this->course->getSlug());

        $form = $crawler->selectButton('Artikel erstellen')->form([
            'article[title]'   => 'Draft Artikel',
            'article[content]' => 'Text ist ein text',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();

        $em = $this->getEntityManager();
        $article = $em->getRepository(Article::class)
            ->findOneBy(['title' => 'Draft Artikel']);

        self::assertNotNull($article);
        self::assertFalse($article->isPublished());
        self::assertNull($article->getPublishedAt());
    }

    public function testShowUnknownArticleReturns404(): void
    {
        $this->client->request('GET', '/articles/unbekannter-slug');

        self::assertResponseStatusCodeSame(404);
    }

}
