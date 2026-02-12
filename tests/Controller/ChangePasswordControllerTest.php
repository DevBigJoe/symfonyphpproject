<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testUserCanChangePassword(): void
    {
        // User anlegen (alle NOT-NULL Felder)
        $user = new User('test@example.com');
        $user->setName('Test User');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'oldPassword123')
        );

        $this->em->persist($user);
        $this->em->flush();

        $userId = $user->getId(); //ID

        // Login
        $this->client->loginUser($user);

        // Seite laden
        $crawler = $this->client->request('GET', '/profile/editPassword');
        self::assertResponseIsSuccessful();

        // Formular absenden
        $form = $crawler->selectButton('Änderung bestätigen')->form([
            'change_password[password][first]'  => 'NewSecurePassword456',
            'change_password[password][second]' => 'NewSecurePassword456',
        ]);

        $this->client->submit($form);

        $updatedUser = $this->em
            ->getRepository(User::class)
            ->find($userId);

        self::assertNotNull($updatedUser);

        // Passwort wurde geaendert
        self::assertTrue(
            $this->passwordHasher->isPasswordValid($updatedUser, 'NewSecurePassword456'),
            'Neues Passwort wurde nicht korrekt gespeichert'
        );

        // Token setzen
        self::assertNotNull($updatedUser->getVerificationToken());
        self::assertNotNull($updatedUser->getVerificationTokenExpiresAt());
    }

    public function testAnonymousUserCannotAccessPasswordChange(): void
    {
        $this->client->request('GET', '/profile/editPassword');

        self::assertResponseStatusCodeSame(401);
    }

    

    public function testPasswordMismatchShowsError(): void
    {

        // User anlegen
        $user = new User('mismatch@example.com');
        $user->setName('Mismatch User');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'oldPassword123')
        );

        $this->em->persist($user);
        $this->em->flush();

        $userId = $user->getId();

        // Login
        $this->client->loginUser($user);

        // Seite laden
        $crawler = $this->client->request('GET', '/profile/editPassword');
        self::assertResponseIsSuccessful();

        // Formular absenden mit unterschiedlichen Passwörtern
        $form = $crawler->selectButton('Änderung bestätigen')->form([
            'change_password[password][first]'  => 'NewPassword123',
            'change_password[password][second]' => 'DifferentPassword456',
        ]);

        $crawler = $this->client->submit($form);

        // Formular sollte erneut angezeigt werden ---> (Fehler)
        self::assertResponseIsSuccessful();

        // Prüfen, dass Fehlermeldung angezeigt wird
        self::assertStringContainsString(
            'Die Passwörter müssen übereinstimmen.',
            $crawler->filter('.form-error')->text()
        );

        // User in DB überprüfen: Passwort darf sich nicht geändert haben
        $updatedUser = $this->em->getRepository(User::class)->find($userId);
        self::assertNotNull($updatedUser);
        self::assertTrue(
            $this->passwordHasher->isPasswordValid($updatedUser, 'oldPassword123'),
            'Passwort darf sich bei Mismatch nicht ändern'
        );
    }

    public function testTokenAndExpirySetAfterPasswordChange(): void
    {

        $user = new User('token@example.com');
        $user->setName('Token User');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'oldPassword123')
        );

        $this->em->persist($user);
        $this->em->flush();

        $userId = $user->getId();

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/profile/editPassword');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Änderung bestätigen')->form([
            'change_password[password][first]'  => 'NewTokenPassword123',
            'change_password[password][second]' => 'NewTokenPassword123',
        ]);

        $this->client->submit($form);

        $updatedUser = $this->em->getRepository(User::class)->find($userId);

        self::assertNotNull($updatedUser);
        self::assertNotNull($updatedUser->getVerificationToken(), 'Token wurde nicht gesetzt');
        self::assertNotNull($updatedUser->getVerificationTokenExpiresAt(), 'Ablaufdatum wurde nicht gesetzt');

        // Ablaufdatum sollte in der Zukunfft liegen
        self::assertGreaterThan(
            new \DateTimeImmutable(),
            $updatedUser->getVerificationTokenExpiresAt()
        );
    }

    public function testPasswordChangeWithoutLoginIsDenied(): void
    {

        // Keine Logindaten für den test

        // Zugriff auf die Seiate ohne Login
        $this->client->request('GET', '/profile/editPassword');

        // HTTP 401 Unauthorized erwartet
        self::assertResponseStatusCodeSame(401);

        // Auch post sollte abgelehnt werden
        //Warum haben wir eingenltich 2 Felder? xD
        $form = [
            'change_password[password][first]'  => 'SomePassword123',
            'change_password[password][second]' => 'SomePassword123',
        ];

        $this->client->request('POST', '/profile/editPassword', $form);

        self::assertResponseStatusCodeSame(401);
    }


        
}
