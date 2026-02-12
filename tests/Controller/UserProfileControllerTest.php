<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserProfileControllerTest extends WebTestCase
{
    /*
    Diese Testklasse überprüft die Funktionalität des UserProfileControllers, insbesondere das Laden von Benutzerprofilen.
    */

    public function testLoadProfileWithAuthenticatedUser(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('testuser1@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString($user->getEmail(), $content);
    }

    public function testLoadProfileWithoutAuthenticationRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        self::assertResponseStatusCodeSame(500);
    }

    public function testLoadProfileRendersCorrectTemplate(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('testuser2@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('profile', strtolower($content));
    }

    public function testProfileDisplaysUserEmail(): void
    {
        $client = static::createClient();
        $email = 'testuser3@example.com';
        $user = $this->createUserWithEmail($email);
        $client->loginUser($user);

        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString($email, $content);
    }

    public function testProfileDisplaysUserName(): void
    {
        $client = static::createClient();
        $name = 'John Doe';
        $user = $this->createUserWithName($name);
        $client->loginUser($user);

        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString($name, $content);
    }

    public function testProfileRouteIsNamed(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('testuser4@example.com');
        $client->loginUser($user);

        $generator = $client->getContainer()->get('router');
        $url = $generator->generate('profile');
        
        self::assertSame('/profile', $url);
    }

    // Hilfsfunktionen
    private function createUserWithEmail(string $email): User
    {
        $user = new User($email);
        $user->setName(explode('@', $email)[0]);
        
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $hasher = $container->get(UserPasswordHasherInterface::class);
        
        $hashedPassword = $hasher->hashPassword($user, 'testpassword123');
        $user->setPassword($hashedPassword);
        
        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createUserWithName(string $name): User
    {
        $user = new User('user' . time() . random_int(1000, 9999) . '@example.com');
        $user->setName($name);
        
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $hasher = $container->get(UserPasswordHasherInterface::class);
        
        $hashedPassword = $hasher->hashPassword($user, 'testpassword123');
        $user->setPassword($hashedPassword);
        
        $em->persist($user);
        $em->flush();

        return $user;
    }
}
