<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Subscription;
use App\Entity\User;
use App\Message\SubscribeToTopicMessage;
use App\MessageHandler\SubscribeToTopicMessageHandler;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testet die Funktionalität des SubscribeToTopicMessageHandler, insbesondere die Verarbeitung von SubscribeToTopicMessage
 * und die Interaktion mit den Repositories und dem EntityManager.
 * Getestet wird die Erstellung neuer Abonnements, das Überspringen von nicht existierenden Benutzern und bereits bestehenden Abonnements,
 * sowie die korrekte Verwendung der Repositories und des EntityManagers.
 */

final class SubscribeToTopicMessageHandlerTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private SubscriptionRepository&MockObject $subscriptionRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private SubscribeToTopicMessageHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new SubscribeToTopicMessageHandler(
            $this->userRepository,
            $this->subscriptionRepository,
            $this->entityManager
        );
    }

    public function testHandlerCreatesNewSubscription(): void
    {
        $userId = 42;
        $topic = 'tech-news';
        $user = $this->createMock(User::class);

        $this->userRepository->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        $this->subscriptionRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::once())
            ->method('persist');

        $this->entityManager->expects(self::once())
            ->method('flush');

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);
    }

    public function testHandlerDoesNothingIfUserNotFound(): void
    {
        $userId = 999;
        $topic = 'tech-news';

        $this->userRepository->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn(null);

        $this->subscriptionRepository->expects(self::never())
            ->method('findOneBy');

        $this->entityManager->expects(self::never())
            ->method('persist');

        $this->entityManager->expects(self::never())
            ->method('flush');

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);
    }

    public function testHandlerDoesNothingIfSubscriptionAlreadyExists(): void
    {
        $userId = 42;
        $topic = 'tech-news';
        $user = $this->createMock(User::class);
        $existingSubscription = $this->createMock(Subscription::class);

        $this->userRepository->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        $this->subscriptionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['user' => $user, 'topic' => $topic])
            ->willReturn($existingSubscription);

        $this->entityManager->expects(self::never())
            ->method('persist');

        $this->entityManager->expects(self::never())
            ->method('flush');

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);
    }

    public function testUserRepositoryIsCalledWithCorrectUserId(): void
    {
        $userId = 123;
        $topic = 'announcements';

        $this->userRepository->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn($this->createMock(User::class));

        $this->subscriptionRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::once())
            ->method('persist');

        $this->entityManager->expects(self::once())
            ->method('flush');

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);
    }

    public function testSubscriptionRepositorySearchesWithCorrectParameters(): void
    {
        $userId = 42;
        $topic = 'test-topic';
        $user = $this->createMock(User::class);

        $this->userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);

        $this->subscriptionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['user' => $user, 'topic' => $topic])
            ->willReturn(null);

        $this->entityManager->expects(self::once())
            ->method('persist');

        $this->entityManager->expects(self::once())
            ->method('flush');

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);
    }

    public function testEntityManagerPersistsSubscriptionWithCorrectData(): void
    {
        $userId = 42;
        $topic = 'tech-news';
        $user = $this->createMock(User::class);

        $this->userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);

        $this->subscriptionRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $persistedSubscription = null;
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->willReturnCallback(function (Subscription $subscription) use ($user, $topic, &$persistedSubscription) {
                $persistedSubscription = $subscription;
                self::assertSame($user, $subscription->getUser());
                self::assertSame($topic, $subscription->getTopic());
            });

        $this->entityManager->expects(self::once())
            ->method('flush');

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);

        self::assertNotNull($persistedSubscription);
    }

    public function testHandlerProcessesMultipleUsersCorrectly(): void
    {
        $userIds = [1, 2, 3];
        $topic = 'news';

        foreach ($userIds as $userId) {
            $user = $this->createMock(User::class);

            $this->userRepository = $this->createMock(UserRepository::class);
            $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
            $this->entityManager = $this->createMock(EntityManagerInterface::class);
            $this->handler = new SubscribeToTopicMessageHandler(
                $this->userRepository,
                $this->subscriptionRepository,
                $this->entityManager
            );

            $this->userRepository->expects(self::once())
                ->method('find')
                ->with($userId)
                ->willReturn($user);

            $this->subscriptionRepository->expects(self::once())
                ->method('findOneBy')
                ->willReturn(null);

            $this->entityManager->expects(self::once())
                ->method('persist');

            $this->entityManager->expects(self::once())
                ->method('flush');

            $message = new SubscribeToTopicMessage($userId, $topic);
            ($this->handler)($message);
        }
    }

    public function testHandlerProcessesMultipleTopicsForSameUser(): void
    {
        $userId = 42;
        $topics = ['tech', 'news', 'announcements'];
        $user = $this->createMock(User::class);

        $this->userRepository->expects(self::any())
            ->method('find')
            ->willReturn($user);

        $this->subscriptionRepository->expects(self::any())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::exactly(count($topics)))
            ->method('persist');

        $this->entityManager->expects(self::exactly(count($topics)))
            ->method('flush');

        foreach ($topics as $topic) {
            $message = new SubscribeToTopicMessage($userId, $topic);
            ($this->handler)($message);
        }
    }

    public function testFlushIsCalledAfterPersist(): void
    {
        $userId = 42;
        $topic = 'news';
        $user = $this->createMock(User::class);

        $this->userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);

        $this->subscriptionRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $callOrder = [];
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'persist';
            });

        $this->entityManager->expects(self::once())
            ->method('flush')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'flush';
            });

        $message = new SubscribeToTopicMessage($userId, $topic);
        ($this->handler)($message);

        self::assertSame(['persist', 'flush'], $callOrder);
    }
}
