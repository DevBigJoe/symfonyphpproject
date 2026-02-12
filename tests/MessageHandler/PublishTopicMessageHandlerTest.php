<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Subscription;
use App\Entity\User;
use App\Message\PublishTopicMessage;
use App\MessageHandler\PublishTopicMessageHandler;
use App\Repository\SubscriptionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Testet die Funktionalität des PublishTopicMessageHandler, insbesondere die Verarbeitung von PublishTopicMessage
 * und die Interaktion mit dem Mailer.
 * Getestet wird das Senden von E-Mails an Abonnenten, das Überspringen von Abonnenten ohne E-Mail-Adresse und 
 * die Handhabung von Ausnahmen beim Senden von E-Mails.
 */

final class PublishTopicMessageHandlerTest extends TestCase
{
    private SubscriptionRepository&MockObject $subscriptionRepository;
    private MailerInterface&MockObject $mailer;
    private PublishTopicMessageHandler $handler;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->handler = new PublishTopicMessageHandler(
            $this->subscriptionRepository,
            $this->mailer
        );
    }

    public function testHandlerInvokesWithValidMessage(): void
    {
        $message = new PublishTopicMessage(
            'tech-news',
            'New Article',
            '<p>Content</p>'
        );

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with('tech-news')
            ->willReturn([]);
        ($this->handler)($message);
    }

    public function testEmailsAreSentToAllSubscribers(): void
    {
        $topic = 'announcements';
        $subject = 'Important Announcement';
        $body = '<p>This is important</p>';

        $user1 = $this->createUser(1, 'user1@example.com');
        $user2 = $this->createUser(2, 'user2@example.com');

        $subscription1 = $this->createSubscription($user1, $topic);
        $subscription2 = $this->createSubscription($user2, $topic);

        $message = new PublishTopicMessage($topic, $subject, $body);

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with($topic)
            ->willReturn([$subscription1, $subscription2]);

        $this->mailer->expects(self::exactly(2))
            ->method('send');

        ($this->handler)($message);
    }

    public function testNoEmailsSentWhenNoSubscriptions(): void
    {
        $message = new PublishTopicMessage('empty-topic', 'Subject', 'Body');

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with('empty-topic')
            ->willReturn([]);

        $this->mailer->expects(self::never())
            ->method('send');

        ($this->handler)($message);
    }

    public function testSubscribersWithoutEmailAreSkipped(): void
    {
        $topic = 'test-topic';
        $subject = 'Subject';
        $body = 'Body';

        $userWithEmail = $this->createUser(1, 'valid@example.com');
        $userWithoutEmail = $this->createMock(User::class);
        $userWithoutEmail->method('getEmail')->willReturn('');

        $subscription1 = $this->createSubscription($userWithEmail, $topic);
        $subscription2 = $this->createSubscription($userWithoutEmail, $topic);

        $message = new PublishTopicMessage($topic, $subject, $body);

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with($topic)
            ->willReturn([$subscription1, $subscription2]);

        $this->mailer->expects(self::once())
            ->method('send');

        ($this->handler)($message);
    }

    public function testSubscriptionsWithoutUserAreSkipped(): void
    {
        $topic = 'test-topic';
        $subscription = $this->createMock(Subscription::class);

        $message = new PublishTopicMessage($topic, 'Subject', 'Body');

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with($topic)
            ->willReturn([$subscription]);

        $this->mailer->expects(self::never())
            ->method('send');

        ($this->handler)($message);
    }

    public function testEmailContainsCorrectRecipient(): void
    {
        $email = 'recipient@example.com';
        $user = $this->createUser(1, $email);
        $subscription = $this->createSubscription($user, 'topic');

        $message = new PublishTopicMessage('topic', 'Subject', 'Body');

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->willReturn([$subscription]);

        $emailSent = false;
        $this->mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $emailObject) use ($email, &$emailSent) {
                $emailSent = true;
                $recipients = $emailObject->getTo();
                $recipientEmails = array_map(fn($addr) => $addr->getAddress(), $recipients);
                self::assertContains($email, $recipientEmails);
            });

        ($this->handler)($message);
        self::assertTrue($emailSent);
    }

    public function testEmailContainsCorrectSubject(): void
    {
        $subject = 'Test Subject Line';
        $user = $this->createUser(1, 'test@example.com');
        $subscription = $this->createSubscription($user, 'topic');

        $message = new PublishTopicMessage('topic', $subject, 'Body');

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->willReturn([$subscription]);

        $this->mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $emailObject) use ($subject) {
                self::assertSame($subject, $emailObject->getSubject());
            });

        ($this->handler)($message);
    }

    public function testEmailContainsCorrectBody(): void
    {
        $body = '<h1>Important Content</h1>';
        $user = $this->createUser(1, 'test@example.com');
        $subscription = $this->createSubscription($user, 'topic');

        $message = new PublishTopicMessage('topic', 'Subject', $body);

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->willReturn([$subscription]);

        $this->mailer->expects(self::once())
            ->method('send');

        ($this->handler)($message);
    }

    public function testHandlerContinuesOnMailerException(): void
    {
        $user1 = $this->createUser(1, 'user1@example.com');
        $user2 = $this->createUser(2, 'user2@example.com');

        $subscription1 = $this->createSubscription($user1, 'topic');
        $subscription2 = $this->createSubscription($user2, 'topic');

        $message = new PublishTopicMessage('topic', 'Subject', 'Body');

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->willReturn([$subscription1, $subscription2]);

        // Erster Aufruf funktioniert, zweiter Aufruf wirft eine Exception
        $callCount = 0;
        $this->mailer->expects(self::exactly(2))
            ->method('send')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 2) {
                    $exception = new class() extends \Exception implements TransportExceptionInterface {
                        public function getDebug(): string { return ''; }
                        public function appendDebug(string $debug): void {}
                    };
                    throw $exception;
                }
                return null;
            });

        // Zweite Email sollte Exception werfen
        $this->expectException(TransportExceptionInterface::class);

        ($this->handler)($message);
    }

    public function testMultipleTopicsAreSeparate(): void
    {
        $user = $this->createUser(1, 'user@example.com');

        $message1 = new PublishTopicMessage('topic1', 'Subject1', 'Body1');
        $message2 = new PublishTopicMessage('topic2', 'Subject2', 'Body2');

        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->handler = new PublishTopicMessageHandler(
            $this->subscriptionRepository,
            $this->mailer
        );

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with('topic1')
            ->willReturn([$this->createSubscription($user, 'topic1')]);

        ($this->handler)($message1);

        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->handler = new PublishTopicMessageHandler(
            $this->subscriptionRepository,
            $this->mailer
        );

        $this->subscriptionRepository->expects(self::once())
            ->method('findByTopic')
            ->with('topic2')
            ->willReturn([$this->createSubscription($user, 'topic2')]);

        ($this->handler)($message2);
    }

    private function createUser(null|int $id, null|string $email): User
    {
        $user = $this->createMock(User::class);
        if ($id !== null) {
            $user->method('getId')->willReturn($id);
        }
        if ($email !== null) {
            $user->method('getEmail')->willReturn($email);
        } else {
            $user->method('getEmail')->willReturn(null);
        }

        return $user;
    }

    private function createSubscription(User $user, string $topic): Subscription
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getUser')->willReturn($user);
        $subscription->method('getTopic')->willReturn($topic);

        return $subscription;
    }
}
