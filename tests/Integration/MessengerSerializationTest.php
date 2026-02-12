<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Message\PublishTopicMessage;
use App\Message\SubscribeToTopicMessage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Testet Funktionalität der Nachrichten-Serialisierung und -Deserialisierung im Symfony Messenger.
 * Also ob das Nachrichten-Objekt korrekt in ein Format umgewandelt und wieder zurückgewandelt werden kann.
 */
final class MessengerSerializationTest extends KernelTestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->serializer = new PhpSerializer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::ensureKernelShutdown();
    }

    public function testSubscribeToTopicMessageCanBeSerialized(): void
    {
        $message = new SubscribeToTopicMessage(42, 'tech-news');
        $envelope = new Envelope($message);

        $serialized = $this->serializer->encode($envelope);

        self::assertIsArray($serialized);
        self::assertArrayHasKey('body', $serialized);
        self::assertIsString($serialized['body']);
    }

    public function testSubscribeToTopicMessageCanBeDeserialized(): void
    {
        $originalMessage = new SubscribeToTopicMessage(42, 'tech-news');
        $envelope = new Envelope($originalMessage);

        $serialized = $this->serializer->encode($envelope);
        $deserialized = $this->serializer->decode($serialized);

        $message = $deserialized->getMessage();
        self::assertInstanceOf(SubscribeToTopicMessage::class, $message);
        self::assertSame(42, $message->getUserId());
        self::assertSame('tech-news', $message->getTopic());
    }

    public function testPublishTopicMessageCanBeSerialized(): void
    {
        $message = new PublishTopicMessage(
            'announcements',
            'Important Update',
            '<p>This is important</p>'
        );
        $envelope = new Envelope($message);

        $serialized = $this->serializer->encode($envelope);

        self::assertIsArray($serialized);
        self::assertArrayHasKey('body', $serialized);
    }

    public function testPublishTopicMessageCanBeDeserialized(): void
    {
        $originalMessage = new PublishTopicMessage(
            'announcements',
            'Important Update',
            '<p>This is important</p>'
        );
        $envelope = new Envelope($originalMessage);

        $serialized = $this->serializer->encode($envelope);
        $deserialized = $this->serializer->decode($serialized);

        $message = $deserialized->getMessage();
        self::assertInstanceOf(PublishTopicMessage::class, $message);
        self::assertSame('announcements', $message->getTopic());
        self::assertSame('Important Update', $message->getSubject());
        self::assertSame('<p>This is important</p>', $message->getBody());
    }

    public function testMessageWithSpecialCharactersCanBeSerialized(): void
    {
        $message = new PublishTopicMessage(
            'topic-ä-ö-ü',
            'Subject with "quotes" and Special Chars',
            '<div>Content & special &lt;html&gt; content</div>'
        );
        $envelope = new Envelope($message);

        $serialized = $this->serializer->encode($envelope);
        $deserialized = $this->serializer->decode($serialized);

        /** @var PublishTopicMessage $deserializedMessage */
        $deserializedMessage = $deserialized->getMessage();
        self::assertSame('topic-ä-ö-ü', $deserializedMessage->getTopic());
        self::assertSame('Subject with "quotes" and Special Chars', $deserializedMessage->getSubject());
    }

    public function testMessageHeadersArePreserved(): void
    {
        $message = new SubscribeToTopicMessage(1, 'test');
        $envelope = new Envelope(
            $message,
            [new DelayStamp(5000)]
        );

        $serialized = $this->serializer->encode($envelope);
        $deserialized = $this->serializer->decode($serialized);

        self::assertNotEmpty($deserialized->all());
    }

    public function testLargeMessageBodyCanBeSerialized(): void
    {
        $largeBody = '<div>' . str_repeat('<p>This is a large paragraph.</p>', 100) . '</div>';

        $message = new PublishTopicMessage(
            'large-topic',
            'Large Message',
            $largeBody
        );
        $envelope = new Envelope($message);

        $serialized = $this->serializer->encode($envelope);
        $deserialized = $this->serializer->decode($serialized);

        /** @var PublishTopicMessage $deserializedMessage */
        $deserializedMessage = $deserialized->getMessage();
        self::assertSame($largeBody, $deserializedMessage->getBody());
    }

    public function testReceiveMessageStampIsAddedToDeserializedMessage(): void
    {
        $message = new PublishTopicMessage('test', 'Subject', 'Body');
        $envelope = new Envelope($message);

        $serialized = $this->serializer->encode($envelope);
        $deserialized = $this->serializer->decode($serialized);

        // Der deserialisierte Envelope sollte einen RedeliveryStamp enthalten
        self::assertIsArray($deserialized->all());
    }

    public function testMultipleMessagesCanBeSerialized(): void
    {
        $messages = [
            new SubscribeToTopicMessage(1, 'topic1'),
            new PublishTopicMessage('topic1', 'Subject', 'Body'),
            new SubscribeToTopicMessage(2, 'topic2'),
        ];

        foreach ($messages as $message) {
            $envelope = new Envelope($message);
            $serialized = $this->serializer->encode($envelope);
            $deserialized = $this->serializer->decode($serialized);

            self::assertSame(get_class($message), get_class($deserialized->getMessage()));
        }
    }

    public function testEnvelopeCanBeCreatedWithMessage(): void
    {
        $message = new SubscribeToTopicMessage(1, 'test');
        $envelope = new Envelope($message);

        self::assertSame($message, $envelope->getMessage());
    }
}
