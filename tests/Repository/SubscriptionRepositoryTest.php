<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SubscriptionRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private SubscriptionRepository $repo;
    private User $user;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $this->repo = self::getContainer()->get(\App\Repository\SubscriptionRepository::class);

        // Passwort-Hasher holen
        $this->passwordHasher = self::getContainer()->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);

        // Einen User für die Tests erstellen
        $this->user = new \App\Entity\User('test@example.com');
        $this->user->setName('Test User');
        $this->user->setPassword($this->passwordHasher->hashPassword($this->user, '12345678'));
        $this->em->persist($this->user);
        $this->em->flush();
    }


    
    public function testAddAndFindByTopic(): void
    {
        $subscription = new Subscription();
        $subscription->setUser($this->user);
        $subscription->setTopic('test-topic');

        $this->repo->add($subscription, true);

        $results = $this->repo->findByTopic('test-topic');

        self::assertCount(1, $results);
        self::assertSame('test-topic', $results[0]->getTopic());
        self::assertSame($this->user->getId(), $results[0]->getUser()->getId());
    }

    public function testIsUserSubscribed(): void
    {
        $subscription = new Subscription();
        $subscription->setUser($this->user);
        $subscription->setTopic('another-topic');

        $this->repo->add($subscription, true);

        // Prüfen, dass isUserSubscribed true liefert
        self::assertTrue(
            $this->repo->isUserSubscribed($this->user, 'another-topic')
        );

        // Prüfen für nicht vorhandene Subscription
        self::assertFalse(
            $this->repo->isUserSubscribed($this->user, 'non-existing-topic')
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Nur EntityManager schließen, nicht auf null setzen
        $this->em->close();
    }
    

    public function testMultipleSubscriptionsPerUser(): void
    {
        // Zweites Topic für denselben User
        $sub1 = new Subscription();
        $sub1->setUser($this->user);
        $sub1->setTopic('topic-1');

        $sub2 = new Subscription();
        $sub2->setUser($this->user);
        $sub2->setTopic('topic-2');

        $this->repo->add($sub1, false);
        $this->repo->add($sub2, true); // flush einmal am Ende

        // Prüfen, dass beide Topics existieren
        $resultsTopic1 = $this->repo->findByTopic('topic-1');
        $resultsTopic2 = $this->repo->findByTopic('topic-2');

        self::assertCount(1, $resultsTopic1);
        self::assertSame('topic-1', $resultsTopic1[0]->getTopic());

        self::assertCount(1, $resultsTopic2);
        self::assertSame('topic-2', $resultsTopic2[0]->getTopic());

        // Prüfen, dass isUserSubscribed für beide Topics true liefert
        self::assertTrue($this->repo->isUserSubscribed($this->user, 'topic-1'));
        self::assertTrue($this->repo->isUserSubscribed($this->user, 'topic-2'));

        // Prüfen für ein nicht vorhandenes Topic
        self::assertFalse($this->repo->isUserSubscribed($this->user, 'topic-3'));
    }


    public function testSubscriptionIntegrityAndRepositoryMethods(): void
    {
        // Neues Subscription-Objekt
        $subscription = new Subscription();
        $subscription->setUser($this->user);
        $subscription->setTopic('comprehensive-topic');

        $this->repo->add($subscription, true);

        // Prüfen, dass Subscription persistiert wurde
        $persisted = $this->em->getRepository(Subscription::class)->findOneBy([
            'topic' => 'comprehensive-topic',
            'user'  => $this->user,
        ]);
        self::assertNotNull($persisted, 'Subscription wurde nicht in der Datenbank gespeichert');

        // Prüfen Typ und Getter
        self::assertInstanceOf(Subscription::class, $persisted);
        self::assertSame($this->user->getId(), $persisted->getUser()->getId());
        self::assertSame('comprehensive-topic', $persisted->getTopic());

        // Prüfen findByTopic liefert das richtige Ergebnis
        $results = $this->repo->findByTopic('comprehensive-topic');
        self::assertIsArray($results);
        self::assertCount(1, $results, 'findByTopic sollte genau 1 Ergebnis liefern');
        self::assertSame($persisted->getId(), $results[0]->getId());

        // Prüfen isUserSubscribed
        self::assertTrue($this->repo->isUserSubscribed($this->user, 'comprehensive-topic'));
        self::assertFalse($this->repo->isUserSubscribed($this->user, 'nonexistent-topic'));

        // Testen, dass das Topic eindeutig gespeichert ist
        $subscriptionDuplicate = new Subscription();
        $subscriptionDuplicate->setUser($this->user);
        $subscriptionDuplicate->setTopic('comprehensive-topic');

        $this->repo->add($subscriptionDuplicate, true);

        $allWithSameTopic = $this->repo->findByTopic('comprehensive-topic');
        self::assertCount(2, $allWithSameTopic, 'Es sollten 2 Subscriptions mit demselben Topic existieren');

        // Prüfen, dass beide Subscriptions denselben User haben
        foreach ($allWithSameTopic as $sub) {
            self::assertSame($this->user->getId(), $sub->getUser()->getId());
        }
    }

    public function testMultipleUsersSubscriptions(): void
    {
        // Zweiter User erstellen
        $user2 = new User('second@example.com');
        $user2->setName('Second User');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, '12345678'));
        $this->em->persist($user2);
        $this->em->flush();

        // Subscription für ersten User
        $sub1 = new Subscription();
        $sub1->setUser($this->user)
            ->setTopic('shared-topic');

        // Subscription für zweiten User
        $sub2 = new Subscription();
        $sub2->setUser($user2)
            ->setTopic('shared-topic');

        $this->repo->add($sub1, false);
        $this->repo->add($sub2, true);

        // Prüfen, dass beide Subscriptions existieren
        $allSubs = $this->repo->findByTopic('shared-topic');
        self::assertCount(2, $allSubs, 'Es sollten 2 Subscriptions für dasselbe Topic existieren');

        // Prüfen, dass isUserSubscribed nur für den richtigen User true liefert
        self::assertTrue($this->repo->isUserSubscribed($this->user, 'shared-topic'));
        self::assertTrue($this->repo->isUserSubscribed($user2, 'shared-topic'));

        // Prüfen, dass die User korrekt zugeordnet sind
        foreach ($allSubs as $sub) {
            self::assertContains($sub->getUser()->getEmail(), ['test@example.com', 'second@example.com']);
        }

        // Prüfen ein Topic, das kein User abonniert hat
        self::assertFalse($this->repo->isUserSubscribed($this->user, 'nonexistent-topic'));
        self::assertFalse($this->repo->isUserSubscribed($user2, 'nonexistent-topic'));
    }

    public function testDuplicateSubscriptionForSameUser(): void
    {
        // Subscription für den Test-User anlegen
        $topic = 'unique-topic';
        $subscription1 = new Subscription();
        $subscription1->setUser($this->user)
                    ->setTopic($topic);

        $this->repo->add($subscription1, true);

        // Prüfen, dass User jetzt abonniert ist
        self::assertTrue($this->repo->isUserSubscribed($this->user, $topic));

        // Zweite Subscription für denselben User und Topic
        $subscription2 = new Subscription();
        $subscription2->setUser($this->user)
                    ->setTopic($topic);

        // hinzufügen
        $this->repo->add($subscription2, true);

        // Prüfen, dass findByTopic nur 2 Einträge enthält (in echten Apps sollte man vielleicht Unique-Constraint setzen)
        $allSubs = $this->repo->findByTopic($topic);
        self::assertCount(2, $allSubs, 'Zwei Subscriptions für denselben User und Topic existieren (Unique-Constraint nicht gesetzt)');

        // Prüfen, dass isUserSubscribed immer noch true ist
        self::assertTrue($this->repo->isUserSubscribed($this->user, $topic));

        // Alle Subscriptions korrekt typisiert
        foreach ($allSubs as $sub) {
            self::assertSame($topic, $sub->getTopic());
            self::assertSame($this->user->getEmail(), $sub->getUser()->getEmail());
        }
    }

    

    public function testFindByTopicWithMultipleUsers(): void
    {
        $topic = 'shared-topic';

        $user2 = new \App\Entity\User('second@example.com');
        $user2->setName('Second User');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, '12345678'));
        $this->em->persist($user2);

        $subscription1 = new Subscription();
        $subscription1->setUser($this->user)->setTopic($topic);

        $subscription2 = new Subscription();
        $subscription2->setUser($user2)->setTopic($topic);

        $this->repo->add($subscription1);
        $this->repo->add($subscription2, true);

        $results = $this->repo->findByTopic($topic);
        self::assertCount(2, $results);
        foreach ($results as $sub) {
            self::assertSame($topic, $sub->getTopic());
        }
    }

    public function testIsUserSubscribedForNonSubscribedTopic(): void
    {
        $topic = 'nonexistent-topic';
        self::assertFalse($this->repo->isUserSubscribed($this->user, $topic));
    }

    public function testMultipleTopicsForSameUser(): void
    {
        $topics = ['topic1', 'topic2', 'topic3'];
        foreach ($topics as $topic) {
            $sub = new Subscription();
            $sub->setUser($this->user)->setTopic($topic);
            $this->repo->add($sub);
        }
        $this->em->flush();

        foreach ($topics as $topic) {
            self::assertTrue($this->repo->isUserSubscribed($this->user, $topic));
        }

        $allSubs = $this->repo->findByTopic('topic1');
        self::assertCount(1, $allSubs);
        self::assertSame('topic1', $allSubs[0]->getTopic());
        self::assertSame($this->user->getEmail(), $allSubs[0]->getUser()->getEmail());
    }

    public function testRemoveSubscription(): void
    {
        $topic = 'removable-topic';
        $sub = new Subscription();
        $sub->setUser($this->user)->setTopic($topic);
        $this->repo->add($sub, true);

        $subsBefore = $this->repo->findByTopic($topic);
        self::assertCount(1, $subsBefore);

        $this->em->remove($sub);
        $this->em->flush();

        $subsAfter = $this->repo->findByTopic($topic);
        self::assertCount(0, $subsAfter);
    }


}
