<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\LogoutSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class LogoutSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        $events = LogoutSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(LogoutEvent::class, $events);
        self::assertSame('onLogout', $events[LogoutEvent::class]);
    }

    public function testOnLogoutSetsRedirectResponse(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->with('home')->willReturn('/');

        $subscriber = new LogoutSubscriber($urlGenerator);

        // Mocks für Event, Token und Request
        $token = $this->createMock(TokenInterface::class);
        $request = $this->createMock(Request::class);
        $event = $this->createMock(LogoutEvent::class);

        $event->method('getToken')->willReturn($token);
        $event->method('getRequest')->willReturn($request);

        // Wir wollen setResponse prüfen
        $event->expects(self::once())->method('setResponse')
            ->with(self::callback(function ($response) {
                return $response instanceof RedirectResponse
                    && $response->getTargetUrl() === '/';
            }));

        $subscriber->onLogout($event);
    }

    public function testOnLogoutWithNullToken(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/');

        $subscriber = new LogoutSubscriber($urlGenerator);

        $request = new Request();
        $event = $this->getMockBuilder(LogoutEvent::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(['getToken', 'getRequest', 'getResponse', 'setResponse'])
                      ->getMock();

        $event->method('getToken')->willReturn(null); // kein Token
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn(null);

        $event->expects($this->once())
              ->method('setResponse')
              ->with(self::isInstanceOf(RedirectResponse::class));

        $subscriber->onLogout($event);
    }

    public function testOnLogoutOverwritesExistingResponse(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/');

        $subscriber = new LogoutSubscriber($urlGenerator);

        $existingResponse = new RedirectResponse('/old');

        $token = $this->createMock(TokenInterface::class);
        $request = new Request();
        $event = $this->getMockBuilder(LogoutEvent::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(['getToken', 'getRequest', 'getResponse', 'setResponse'])
                      ->getMock();

        $event->method('getToken')->willReturn($token);
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn($existingResponse);

        $event->expects($this->once())
              ->method('setResponse')
              ->with(self::callback(function ($response) {
                  return $response instanceof RedirectResponse
                      && $response->getTargetUrl() === '/';
              }));

        $subscriber->onLogout($event);
    }

    public function testOnLogoutCreatesRedirectResponse(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with('home')
            ->willReturn('/');

        $subscriber = new LogoutSubscriber($urlGenerator);

        $token = $this->createMock(TokenInterface::class);
        $request = new Request();
        $event = $this->getMockBuilder(LogoutEvent::class)
                      ->disableOriginalConstructor()
                      ->onlyMethods(['getToken', 'getRequest', 'getResponse', 'setResponse'])
                      ->getMock();

        $event->method('getToken')->willReturn($token);
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn(null);

        $event->expects($this->once())
              ->method('setResponse')
              ->with(self::callback(function ($response) {
                  return $response instanceof RedirectResponse
                      && $response->getTargetUrl() === '/';
              }));

        $subscriber->onLogout($event);
    }
}
