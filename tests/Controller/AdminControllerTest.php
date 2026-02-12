<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Doctrine\ORM\EntityManagerInterface;

final class AdminControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $testUser;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        // EntityManager holen
        $em = self::getContainer()->get(EntityManagerInterface::class);

        // Test-User erstellen
        $this->testUser = new User('user@test.de')
            ->setName('Test User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test')
            ->setIsVerified(true);

        $em->persist($this->testUser);
        $em->flush();

        // Einloggen
        $this->client->loginUser($this->testUser);
    }

    public function testUsersRouteReturnsOk(): void
    {
        $this->client->request('GET', '/admin/users');

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Admin Users-Route sollte 200 zurückgeben');
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'HTML sollte enthalten sein');
    }

    public function testShowUserInfoRouteReturnsOk(): void
    {
        $this->client->request('GET', '/admin/users/' . $this->testUser->getId());

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Show User-Route sollte 200 zurückgeben');
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'HTML sollte enthalten sein');
    }

    public function testUsersRouteWithNoUsersReturnsOk(): void
    {
        // Alle User außer Test-User löschen
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $users = $em->getRepository(\App\Entity\User::class)->findAll();
        foreach ($users as $user) {
            if ($user->getId() !== $this->testUser->getId()) {
                $em->remove($user);
            }
        }
        $em->flush();

        $this->client->request('GET', '/admin/users');
        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Admin Users-Route sollte 200 zurückgeben, auch wenn keine weiteren Users existieren');
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'HTML sollte enthalten sein');
    }

    public function testShowUserInfoWithInvalidIdReturnsNotFound(): void
    {
        $invalidId = 999999; // ID, die nicht existiert
        $this->client->request('GET', '/admin/users/' . $invalidId);

        $response = $this->client->getResponse();

        self::assertSame(404, $response->getStatusCode(), 'Show User-Route sollte 404 zurückgeben, wenn User nicht existiert');
    }

    public function testUsersListRendersHtml(): void
    {
        $this->client->request('GET', '/admin/users');
        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'Users list sollte HTML enthalten');
    }

}
