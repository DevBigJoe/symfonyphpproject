<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Degree;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Subscription;

final class DegreeControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $em = $this->getEntityManager();
        $normalUser = new User('user@test.de')
            -> setName('User')
            -> setRoles(['ROLE_REDAKTION'])
            -> setPassword('testtest')
            -> setIsVerified(true);
        $em->persist($normalUser);
        $em->flush();
        $this->client->loginUser($normalUser);
    }

    public function testListDegreesSuccessfully(): void
    {
        $em = $this->getEntityManager();
        $degree = new Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);
        $degree2 = new Degree();
        $degree2->setName('Test Degree 2');
        $degree2->setDescription('Test Description 2');
        $degree2->initSlug();
        $em->persist($degree2);
        $em->flush();
        $this->client->request('GET', '/degrees/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Studiengänge');
        self::assertSelectorTextContains('body', 'Test Degree');
        self::assertSelectorTextContains('body', 'Test Degree 2');
    }

    public function testSubscribeToDegreeSuccessfully(): void
    {
        $em = $this->getEntityManager();
        $degree = new Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);
        $em->flush();
        $this->client->request('POST', '/degrees/subscribe/' . $degree->getSlug());
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertRouteSame('degrees_show', ['slug' => $degree->getSlug()]);
        self::assertSelectorTextContains('body', 'Studiengang abonniert');
        
        self::assertResponseIsSuccessful();
        $em = $this->getEntityManager();
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['topic' => $degree->getSlug()]);

        self::assertNotNull($subscription);
    }

    public function testUnsubscribeFromDegreeSuccessfully(): void
    {
        $em = $this->getEntityManager();
        $degree = new Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);
        $em->flush();
        $this->client->request('POST', '/degrees/subscribe/' . $degree->getSlug());

        $this->client->request('POST', '/degrees/subscribe/' . $degree->getSlug());
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertRouteSame('degrees_show', ['slug' => $degree->getSlug()]);
        self::assertSelectorTextContains('body', 'Studiengang abonnieren');
        $em = $this->getEntityManager();
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['topic' => $degree->getSlug()]);
        self::assertNull($subscription);
    }

    public function testSubscribeToDegreeUnauthenticatedFails(): void
    {
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->client->request('POST', '/degrees/subscribe/' . 'some-slug');
        self::assertResponseRedirects('/login');
    }

    public function testCreateDegreeSuccessfully(): void
    {
        $this->client->request('GET', '/degrees/new');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Neuen Studiengang erstellen');
        $this->client->submitForm('Studiengang erstellen', [
            'degree[name]' => 'Test Degree',
            'degree[description]' => 'Test Description',
        ]);
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Studiengänge');
        $em = $this->getEntityManager();
        $degree = $em->getRepository(Degree::class)->findOneBy(['name' => 'Test Degree']);
        self::assertNotNull($degree);
        self::assertSame('Test Degree', $degree->getName());
        self::assertSame('Test Description', $degree->getDescription());
    }

    public function testCantCreateDuplicateDegree(): void
    {
        $em = $this->getEntityManager();
        $degree = new Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);
        $em->flush();

        $this->client->request('GET', '/degrees/new');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Studiengang erstellen', [
            'degree[name]' => 'Test Degree',
            'degree[description]' => 'Test Description',
        ]);
        self::assertResponseIsSuccessful();
    }

    public function testShowDegreeSuccessfully(): void
    {
        $em = $this->getEntityManager();
        $degree = new Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);
        $em->flush();
        $this->client->request('GET', '/degrees/' . $degree->getSlug());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Test Degree');
    }

    public function testShowDegreeNotFound(): void
    {
        $this->client->request('GET', '/degrees/not-found');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}