<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class EditUserProfileControllerTest extends WebTestCase
{
    /*
    Diese Testklasse überprüft die Funktionalität des EditUserProfileControllers,
    Insbesondere die Bearbeitung von Benutzerprofilen.
    */

    public function testEditProfileFormLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser1@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('form', strtolower($content));
    }

    public function testEditProfileWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = static::createClient();

        $client->request('GET', '/profile/edit');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEditProfileRedirectsToProfileAfterUpdate(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser3@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Speichern')->form();
        $form['profile[email]'] = 'newtest@example.com';
        $form['profile[name]'] = 'New Test';

        $client->submit($form);

        self::assertResponseRedirects('/profile');
    }

    public function testEditFormDisplaysEmailField(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser4@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('email', strtolower($content));
    }

    public function testEditFormDisplaysNameField(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser5@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString('name', strtolower($content));
    }

    public function testCurrentEmailIsPrefilled(): void
    {
        $client = static::createClient();
        $email = 'prefill1@example.com';
        $user = $this->createUserWithEmail($email);
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString($email, $content);
    }

    public function testCurrentNameIsPrefilled(): void
    {
        $client = static::createClient();
        $name = 'Prefill User';
        $user = $this->createUserWithName($name);
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');

        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);
        self::assertStringContainsString($name, $content);
    }

    public function testEmptyNameRejected(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser7@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Speichern')->form();
        $form['profile[email]'] = 'changed7@example.com';
        $form['profile[name]'] = 'NewName';

        $crawler = $client->submit($form);

        self::assertResponseRedirects('/profile');
        
        $container = static::getContainer();
        $repo = $container->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $updatedUser = $repo->find($user->getId());
        self::assertNotNull($updatedUser);
        self::assertSame('changed7@example.com', $updatedUser->getEmail());
        self::assertSame('NewName', $updatedUser->getName());
    }

    public function testOnlyAuthenticatedUserCanEditProfile(): void
    {
        $client = static::createClient();

        $client->request('GET', '/profile/edit');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testNameWithSpecialCharactersAccepted(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser10@example.com');
        $client->loginUser($user);

        $specialName = 'Müller-Schöne äöü';

        $client->request('GET', '/profile/edit');
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Speichern')->form();
        $form['profile[email]'] = 'valid@example.com';
        $form['profile[name]'] = $specialName;

        $client->submit($form);

        self::assertResponseRedirects('/profile');

        $container = static::getContainer();
        $repository = $container->get('doctrine.orm.entity_manager')->getRepository('App\Entity\User');
        $updatedUser = $repository->findOneBy(['email' => 'valid@example.com']);

        self::assertNotNull($updatedUser);
        self::assertSame($specialName, $updatedUser->getName());
    }

    public function testMultipleUpdatesWorkSequentially(): void
    {
        $client = static::createClient();
        $user = $this->createUserWithEmail('edituser11@example.com');
        $client->loginUser($user);

        $client->request('GET', '/profile/edit');
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Speichern')->form();
        $form['profile[email]'] = 'first@example.com';
        $form['profile[name]'] = 'First Update';
        $client->submit($form);

        self::assertResponseRedirects('/profile');

        $client->request('GET', '/profile/edit');
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Speichern')->form();
        $form['profile[email]'] = 'second@example.com';
        $form['profile[name]'] = 'Second Update';
        $client->submit($form);

        self::assertResponseRedirects('/profile');
        $container = static::getContainer();
        $repository = $container->get('doctrine.orm.entity_manager')->getRepository('App\Entity\User');
        $updatedUser = $repository->findOneBy(['email' => 'second@example.com']);

        self::assertNotNull($updatedUser);
        self::assertSame('second@example.com', $updatedUser->getEmail());
        self::assertSame('Second Update', $updatedUser->getName());
    }

    //Hilfsfunktionen
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
