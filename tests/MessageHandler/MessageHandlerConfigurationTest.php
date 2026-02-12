<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\MessageHandler\PublishTopicMessageHandler;
use App\MessageHandler\SubscribeToTopicMessageHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use ReflectionClass;

/**
 * Test überprüft die Konfiguration der MessageHandler-Klassen, 
 * insbesondere ob sie korrekt mit #[AsMessageHandler] annotiert sind, die __invoke-Methode haben und ob sie readonly sind. 
 * Außerdem wird überprüft, ob die Konstruktoren die erwarteten Abhängigkeiten haben.
 */
final class MessageHandlerConfigurationTest extends TestCase
{
    public function testPublishTopicMessageHandlerHasAsMessageHandlerAttribute(): void
    {
        $reflection = new ReflectionClass(PublishTopicMessageHandler::class);

        $attributes = $reflection->getAttributes(AsMessageHandler::class);

        self::assertNotEmpty($attributes, 'PublishTopicMessageHandler must have #[AsMessageHandler] attribute');
    }

    public function testSubscribeToTopicMessageHandlerHasAsMessageHandlerAttribute(): void
    {
        $reflection = new ReflectionClass(SubscribeToTopicMessageHandler::class);

        $attributes = $reflection->getAttributes(AsMessageHandler::class);

        self::assertNotEmpty($attributes, 'SubscribeToTopicMessageHandler must have #[AsMessageHandler] attribute');
    }

    public function testPublishTopicMessageHandlerHasInvokeMethod(): void
    {
        $reflection = new ReflectionClass(PublishTopicMessageHandler::class);

        self::assertTrue(
            $reflection->hasMethod('__invoke'),
            'PublishTopicMessageHandler must have __invoke method'
        );

        $method = $reflection->getMethod('__invoke');
        self::assertTrue($method->isPublic(), '__invoke method must be public');
    }

    public function testSubscribeToTopicMessageHandlerHasInvokeMethod(): void
    {
        $reflection = new ReflectionClass(SubscribeToTopicMessageHandler::class);

        self::assertTrue(
            $reflection->hasMethod('__invoke'),
            'SubscribeToTopicMessageHandler must have __invoke method'
        );

        $method = $reflection->getMethod('__invoke');
        self::assertTrue($method->isPublic(), '__invoke method must be public');
    }

    public function testPublishTopicMessageHandlerIsReadonly(): void
    {
        $reflection = new ReflectionClass(PublishTopicMessageHandler::class);

        self::assertTrue(
            $reflection->isReadOnly(),
            'PublishTopicMessageHandler should be readonly'
        );
    }

    public function testSubscribeToTopicMessageHandlerIsReadonly(): void
    {
        $reflection = new ReflectionClass(SubscribeToTopicMessageHandler::class);

        self::assertTrue(
            $reflection->isReadOnly(),
            'SubscribeToTopicMessageHandler should be readonly'
        );
    }

    public function testPublishTopicMessageHandlerHasCorrectDependencies(): void
    {
        $reflection = new ReflectionClass(PublishTopicMessageHandler::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor, 'Handler must have a constructor');

        $parameters = $constructor->getParameters();
        self::assertCount(2, $parameters, 'PublishTopicMessageHandler should have 2 dependencies');

        $parameterNames = array_map(fn($p) => $p->getName(), $parameters);
        self::assertContains('subscriptionRepository', $parameterNames);
        self::assertContains('mailer', $parameterNames);
    }

    public function testSubscribeToTopicMessageHandlerHasCorrectDependencies(): void
    {
        $reflection = new ReflectionClass(SubscribeToTopicMessageHandler::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor, 'Handler must have a constructor');

        $parameters = $constructor->getParameters();
        self::assertCount(3, $parameters, 'SubscribeToTopicMessageHandler should have 3 dependencies');

        $parameterNames = array_map(fn($p) => $p->getName(), $parameters);
        self::assertContains('userRepository', $parameterNames);
        self::assertContains('subscriptionRepository', $parameterNames);
        self::assertContains('em', $parameterNames);
    }
}
