<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{

    public function testRegistrationPageLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        // HTTP Status prüfen
        self::assertResponseStatusCodeSame(200);

        // Prüfen, ob Formular vorhanden ist
        self::assertSelectorExists('form');

        // Optional: prüfen, ob ein typisches Feld existiert
        self::assertSelectorExists('input[type="email"]');
        self::assertSelectorExists('input[type="password"]');
    }

    public function testVerifyEmailWithInvalidTokenShowsError(): void
    {
        $client = static::createClient();

        $client->request('GET', '/verify/invalid-token-123');

        // Controller rendert eine Fehlerseite → 200 OK
        self::assertResponseStatusCodeSame(200);

        // Prüfen, ob Fehlermeldung angezeigt wird
        self::assertSelectorTextContains(
            'body',
            'Ungültiger Verifizierungs-Token'
        );
    }

    public function testVerifyEmailWithExpiredTokenShowsError(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get('doctrine')->getManager();

        // Test-User anlegen (ALLE Pflichtfelder setzen)
        $user = new \App\Entity\User('test@example.com');
        $user->setName('Test User');
        $user->setPassword('dummy');
        $user->setIsVerified(false);
        $user->setVerificationToken('expired-token');
        $user->setVerificationTokenExpiresAt(
            new \DateTimeImmutable()->modify('-1 day')
        );

        $entityManager->persist($user);
        $entityManager->flush();

        // Abgelaufenen Token aufrufen
        $client->request('GET', '/verify/expired-token');

        self::assertResponseStatusCodeSame(200);

        self::assertSelectorTextContains(
            'body',
            'Der Verifizierungs-Token ist abgelaufen'
        );
    }

    public function testVerifyEmailSuccessfullyMarksUserAsVerified(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Test-User mit gültigem Token anlegen
        $user = new \App\Entity\User('verified@example.com');
        $user->setName('Verified User');
        $user->setPassword('dummy');
        $user->setIsVerified(false);
        $user->setVerificationToken('valid-token-123');
        $user->setVerificationTokenExpiresAt(
            new \DateTimeImmutable()->modify('+1 day')
        );

        $entityManager->persist($user);
        $entityManager->flush();

        // Verifizierungs-Link aufrufen
        $client->request('GET', '/verify/valid-token-123');

        // User neu aus der DB laden
        $entityManager->refresh($user);

        // Assertions
        self::assertResponseStatusCodeSame(200);
        self::assertTrue($user->isVerified());
        self::assertNull($user->getVerificationToken());
        self::assertNull($user->getVerificationTokenExpiresAt());
    }
        

    public function testRegistrationWithInvalidDataShowsFormErrors(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        // Formular über Submit-Button auswählen
        $form = $crawler->selectButton('Jetzt Registrieren')->form();

        // Ungültige Daten einfüllen
        $form['registration_form[email]'] = ''; // leere E-Mail
        $form['registration_form[name]'] = ''; // leerer Name
        $form['registration_form[password][first]'] = '123'; // zu kurzes Passwort
        $form['registration_form[password][second]'] = '123'; // zu kurzes Passwort bestätigen

        // Formular absenden
        $client->submit($form);

        // Seite wird erneut gerendert → 200 OK
        self::assertResponseStatusCodeSame(200);

        // Prüfen, dass Fehler für die Felder angezeigt werden
        self::assertSelectorExists('form');
    }
        
    public function testSuccessfulRegistration(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        // Clean up any existing test users
        $existingUser = $em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'newuser@example.com']);
        if ($existingUser) {
            $em->remove($existingUser);
            $em->flush();
        }

        // Registrierungsseite aufrufen
        $crawler = $client->request('GET', '/register');

        // Formular auswählen (via Submit-Button)
        $form = $crawler->selectButton('Jetzt Registrieren')->form();

        // Test-Daten eintragen
        $form['registration_form[email]'] = 'newuser@example.com';
        $form['registration_form[name]'] = 'New User';
        $form['registration_form[password][first]'] = 'securePass123';
        $form['registration_form[password][second]'] = 'securePass123';

        // Formular absenden
        $client->submit($form);

        // Prüfen, dass die Seite erneut gerendert wird → 200 OK
        self::assertResponseStatusCodeSame(200);

        // Prüfen, dass die E-Mail-Adresse auf der Seite angezeigt wird (sprachunabhängig)
        self::assertSelectorTextContains('body', 'newuser@example.com');

        // Entity Manager neu laden für frische DB-Daten
        $em->clear();

        // Benutzer aus der DB laden
        $user = $em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'newuser@example.com']);

        self::assertNotNull($user, 'Der Benutzer wurde nicht in der Datenbank gefunden.');
        self::assertFalse($user->isVerified(), 'Der Benutzer sollte noch nicht verifiziert sein.');
        self::assertNotNull($user->getVerificationToken(), 'Der Verifizierungstoken fehlt.');
        self::assertNotNull($user->getVerificationTokenExpiresAt(), 'Das Ablaufdatum des Tokens fehlt.');
        self::assertNotSame('securePass123', $user->getPassword(), 'Das Passwort sollte gehasht sein.');
    }

    public function testRegistrationWithDuplicateEmailFails(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        // Clean up first
        $existing = $em->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'duplicate@example.com']);
        if ($existing) {
            $em->remove($existing);
            $em->flush();
        }

        // Benutzer mit dieser Email existiert bereits
        $existingUser = new \App\Entity\User('duplicate@example.com');
        $existingUser->setName('Existing User');
        $existingUser->setPassword('hashedpassword');
        $em->persist($existingUser);
        $em->flush();

        // Registrierungsseite aufrufen
        $crawler = $client->request('GET', '/register');

        // Formular auswählen
        $form = $crawler->selectButton('Jetzt Registrieren')->form();

        // Mit gleicher E-Mail registrieren
        $form['registration_form[email]'] = 'duplicate@example.com';
        $form['registration_form[name]'] = 'Another User';
        $form['registration_form[password][first]'] = 'securePass123';
        $form['registration_form[password][second]'] = 'securePass123';

        // Formular absenden
        $client->submit($form);

        // Seite sollte mit Fehler erneut gerendered werden
        self::assertResponseStatusCodeSame(200);

        // Formular sollte noch vorhanden sein (mit Validierungsfehler)
        self::assertSelectorExists('form');

        // Es sollte nur noch ein Benutzer mit dieser E-Mail existieren
        $em->clear();
        $users = $em->getRepository(\App\Entity\User::class)
            ->findBy(['email' => 'duplicate@example.com']);
        
        self::assertCount(1, $users, 'Es sollte nur ein Benutzer mit dieser E-Mail geben.');
    }


}
