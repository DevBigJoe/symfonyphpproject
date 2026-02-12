<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Degree;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Course;
use App\Entity\Subscription;

final class CourseControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Degree $degree;
    private Course $course;

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

        $this->degree = new Degree();
        $this->degree->setName('Test Degree');
        $this->degree->setDescription('Test Description');
        $this->degree->initSlug();
        $em->persist($this->degree);
        $this->course = new Course();
        $this->course->setName('Test Course');
        $this->course->setDescription('Test Description');
        $this->course->setDegree($this->degree);
        $this->course->initSlug();
        $em->persist($this->course);
        $em->flush();
    }

    public function testShowCourseSuccessfully(): void
    {
        $this->client->request('GET', '/courses/' . $this->course->getSlug());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Test Course');
        self::assertSelectorTextContains('body', 'Lernunterlagen');
    }

    public function testShowCourseNotFound(): void
    {
        $this->client->request('GET', '/courses/not-found-slug');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testSubscribeToCourseSuccessfully(): void
    {
        $this->client->request('POST', '/courses/subscribe/' . $this->course->getSlug());
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertRouteSame('courses_show', ['slug' => $this->course->getSlug()]);
        self::assertSelectorTextContains('body', 'Kurs abonniert');
        
        $em = $this->getEntityManager();
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['topic' => $this->course->getSlug()]);
        self::assertNotNull($subscription);
    }

    public function testUnsubscribeFromCourseSuccessfully(): void
    {
        $em = $this->getEntityManager();
        $this->client->request('POST', '/courses/subscribe/' . $this->course->getSlug());
        $this->client->request('POST', '/courses/subscribe/' . $this->course->getSlug());
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertRouteSame('courses_show', ['slug' => $this->course->getSlug()]);
        self::assertSelectorTextContains('body', 'Kurs abonnieren');
        $em = $this->getEntityManager();
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['topic' => $this->course->getSlug()]);
        self::assertNull($subscription);
    }

    public function testSubscribeToCourseUnauthenticatedFails(): void
    {
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->client->request('POST', '/courses/subscribe/' . 'some-slug');
        self::assertResponseRedirects('/login');
    }

    public function testCreateCourseSuccessfully(): void
    {
        $this->client->request('GET', '/courses/' . $this->degree->getSlug() . '/new');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Neuen Kurs erstellen');
        $this->client->submitForm('Kurs erstellen', [
            'course[name]' => 'Test Course 2',
            'course[description]' => 'Test Description',
        ]);
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        $em = $this->getEntityManager();
        $course = $em->getRepository(Course::class)->findOneBy(['name' => 'Test Course 2']);
        self::assertNotNull($course);
        self::assertSame('Test Course 2', $course->getName());
        self::assertSame('Test Description', $course->getDescription());
    }

    public function testCantCreateDuplicateCourse(): void
    {
        $this->client->request('GET', '/courses/' . $this->degree->getSlug() . '/new');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Kurs erstellen', [
            'course[name]' => 'Test Course',
            'course[description]' => 'Test Description',
        ]);
        self::assertResponseIsSuccessful();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}