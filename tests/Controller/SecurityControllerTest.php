<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        // EntityManager holen
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        // Test-User erstellen
        $user = new User('user@test.de')
            ->setName('User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test')
            ->setIsVerified(true);

        $em->persist($user);
        $em->flush();
    }

    public function testLoginPageRendersForAnonymous(): void
    {
        $this->client->request('GET', '/login');

        $response = $this->client->getResponse();

        self::assertSame(200, $response->getStatusCode(), 'Login-Seite sollte 200 zurückgeben');
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('<!DOCTYPE html>', $content, 'Login-HTML sollte enthalten sein');
    }
}
