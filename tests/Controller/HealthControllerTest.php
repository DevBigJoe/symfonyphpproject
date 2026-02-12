<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class HealthControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        // EntityManager holen
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        // Test-User erstellen (wie im ArticleControllerTest)
        $user = new User('user@test.de')
            ->setName('User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test')
            ->setIsVerified(true);

        $em->persist($user);
        $em->flush();

        // User einloggen, damit HealthController Zugriff erlaubt
        $this->client->loginUser($user);
    }

    public function testHealthRouteReturnsOk(): void
    {
        $this->client->request('GET', '/health');

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Health-Check sollte 200 zurückgeben');

        $content = $response->getContent();
        self::assertNotFalse($content);
        $data = json_decode($content, true);
        self::assertIsArray($data);
        self::assertSame('ok', $data['status']);
        self::assertArrayHasKey('database', $data);
        self::assertArrayHasKey('users', $data);
        self::assertArrayHasKey('articles', $data);
        self::assertArrayHasKey('activeUsers', $data);
        self::assertArrayHasKey('loginPage', $data);
    }

    public function testDatadumpRouteReturnsOk(): void
    {
        $this->client->request('GET', '/health/datadump');

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Datadump sollte 200 zurückgeben');

        $content = $response->getContent();
        self::assertNotFalse($content);
        $data = json_decode($content, true);
        self::assertIsArray($data);
        self::assertSame('ok', $data['status']);
        self::assertArrayHasKey('articles', $data);
        self::assertIsArray($data['articles']);
    }

    public function testDashboardRouteRendersSuccessfully(): void
    {
        $this->client->request('GET', '/health/dashboard');

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Dashboard sollte 200 zurückgeben');
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'Dashboard-HTML sollte enthalten sein');
    }

    public function testPrintPdfRouteReturnsPdf(): void
    {
        // PDF-Route erwartet POST mit articleId
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        // Test-Degree erstellen
        $degree = new \App\Entity\Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);

        // Test-Course erstellen
        $course = new \App\Entity\Course();
        $course->setName('Test Course');
        $course->setDescription('Test Description');
        $course->setDegree($degree);
        $course->initSlug();
        $em->persist($course);

        // Test-Artikel erstellen
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user@test.de']);
        self::assertNotNull($user);
        $article = new \App\Entity\Article();
        $article->setTitle('Test PDF Article');
        $article->setContent('PDF Content');
        $article->setAuthor($user);
        $article->setCourse($course);

        $em->persist($article);
        $em->flush();

        $this->client->request('GET', '/health/datadump/' . $article->getId() . '/pdf');

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'PDF-Route sollte 200 zurückgeben');
        self::assertSame('application/pdf', $response->headers->get('Content-Type'), 'Content-Type sollte PDF sein');
    }

    public function testDashboardRendersHtmlSuccessfully(): void
    {
        // GET Request auf Dashboard
        $this->client->request('GET', '/health/dashboard');

        $response = $this->client->getResponse();

        // Prüfen, dass Statuscode 200 ist
        self::assertSame(200, $response->getStatusCode(), 'Dashboard sollte 200 zurückgeben');

        // Prüfen, dass HTML vorhanden ist
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'Dashboard HTML sollte enthalten sein');
    }

    public function testHealthJsonStructure(): void
    {
        $this->client->request('GET', '/health');
        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        self::assertNotFalse($content);
        $data = json_decode($content, true);

        self::assertIsArray($data);
        self::assertArrayHasKey('status', $data);
        self::assertArrayHasKey('database', $data);
    }

    public function testDatadumpReturnsArticlesArray(): void
    {
        $this->client->request('GET', '/health/datadump');
        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        self::assertNotFalse($content);
        $data = json_decode($content, true);

        self::assertIsArray($data['articles']);
    }

    public function testDashboardHtmlIsNotEmpty(): void
    {
        $this->client->request('GET', '/health/dashboard');
        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content);
    }
}
