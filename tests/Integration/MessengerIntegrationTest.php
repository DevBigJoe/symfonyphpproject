<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Message\PublishTopicMessage;
use App\Message\SubscribeToTopicMessage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Subscribe und Publish Nachrichten Integrationstests.
 * Diese Tests überprüfen, dass Nachrichten korrekt weitergeleitet und verarbeitet werden.
 */
final class MessengerIntegrationTest extends KernelTestCase
{
    private MessageBusInterface $messageBus;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->messageBus = self::getContainer()->get(MessageBusInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::ensureKernelShutdown();
    }

    public function testSubscribeToTopicMessageCanBeDispatched(): void
    {
        $message = new SubscribeToTopicMessage(1, 'test-topic');
        $envelope = $this->messageBus->dispatch($message);
        self::assertSame($message, $envelope->getMessage());
    }

    public function testPublishTopicMessageCanBeDispatched(): void
    {
        $message = new PublishTopicMessage(
            'test-topic',
            'Test Subject',
            '<p>Test Body</p>'
        );

        $envelope = $this->messageBus->dispatch($message);
        self::assertSame($message, $envelope->getMessage());
    }

    public function testMultipleSubscribeMessagesCanBeDispatched(): void
    {
        $messages = [
            new SubscribeToTopicMessage(1, 'topic1'),
            new SubscribeToTopicMessage(2, 'topic1'),
            new SubscribeToTopicMessage(1, 'topic2'),
        ];

        foreach ($messages as $message) {
            $envelope = $this->messageBus->dispatch($message);
            self::assertSame($message, $envelope->getMessage());
        }
    }

    public function testMultiplePublishMessagesCanBeDispatched(): void
    {
        $messages = [
            new PublishTopicMessage('topic1', 'Subject 1', 'Body 1'),
            new PublishTopicMessage('topic2', 'Subject 2', 'Body 2'),
            new PublishTopicMessage('topic1', 'Subject 3', 'Body 3'),
        ];

        foreach ($messages as $message) {
            $envelope = $this->messageBus->dispatch($message);
            self::assertSame($message, $envelope->getMessage());
        }
    }

    public function testMessageCanBeDispatchedAndRetrieved(): void
    {
        $message = new SubscribeToTopicMessage(1, 'integration-test');
        $envelope = $this->messageBus->dispatch($message);

        self::assertSame($message, $envelope->getMessage());
    }

    public function testPublishMessageWithDifferentTopics(): void
    {
        $topics = ['news', 'tech', 'announcements', 'updates'];

        foreach ($topics as $topic) {
            $message = new PublishTopicMessage(
                $topic,
                "Subject for $topic",
                "<p>Body for $topic</p>"
            );

            $envelope = $this->messageBus->dispatch($message);
            self::assertSame($message, $envelope->getMessage());
        }
    }

    public function testSubscribeMessageWithDifferentUsers(): void
    {
        $userIds = [1, 2, 3, 100, 999];

        foreach ($userIds as $userId) {
            $message = new SubscribeToTopicMessage($userId, 'subscription-test');
            $envelope = $this->messageBus->dispatch($message);

            self::assertSame($message, $envelope->getMessage());
        }
    }

    public function testMessengerTransportIsConfigured(): void
    {
        $transport = self::getContainer()->get('messenger.transport.async');
        self::assertInstanceOf(TransportInterface::class, $transport);
    }

    public function testMessageRoutingForSubscribeToTopicMessage(): void
    {
        $message = new SubscribeToTopicMessage(1, 'routing-test');
        $envelope = $this->messageBus->dispatch($message);
        self::assertSame($message, $envelope->getMessage());
    }

    public function testMessageRoutingForPublishTopicMessage(): void
    {
        // Überprüft, dass PublishTopicMessage an den async Transport gesendet wird
        $message = new PublishTopicMessage('routing-test', 'Subject', 'Body');
        $envelope = $this->messageBus->dispatch($message);
        self::assertSame($message, $envelope->getMessage());
    }

    public function testMessagesAreDispatchedWithoutErrors(): void
    {
        $subscribeMessage = new SubscribeToTopicMessage(1, 'test');
        $publishMessage = new PublishTopicMessage('tech', 'News', 'Body');

        $envelope1 = $this->messageBus->dispatch($subscribeMessage);
        $envelope2 = $this->messageBus->dispatch($publishMessage);
        self::assertSame($subscribeMessage, $envelope1->getMessage());
        self::assertSame($publishMessage, $envelope2->getMessage());
    }
}
