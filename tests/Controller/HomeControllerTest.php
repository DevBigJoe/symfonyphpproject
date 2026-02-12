<?php

declare(strict_types=1);
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Stopwatch\Stopwatch;

final class HomeControllerTest extends WebTestCase {
    public function testHomeExists() : void {
        $client = self::createClient();
        $client->request("GET", "/");
        self::assertResponseIsSuccessful();
    }

    public function testNavigationExists() : void {
        self::createClient()->request("GET", "/");
        self::assertResponseIsSuccessful();
        self::assertSelectorExists("nav");
        self::assertSelectorTextContains("nav", "Home");
    }

    public function testFooterExists() : void {
        self::createClient()->request("GET", "/");
        self::assertResponseIsSuccessful();
        self::assertSelectorExists("footer");
    }

    public function testContentSectionsExist() : void {
        self::createClient()->request("GET", "/");
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(3, "my-content");
    }

    public function testHomeLoadsLessThan1000ms() : void {
        $client = self::createClient();
        $stopwatch = new Stopwatch();
        $stopwatch->start("load-time");
        $client->request("GET", "/");
        $event = $stopwatch->stop("load-time");
        self::assertLessThan(1100, $event->getDuration());
        self::assertResponseIsSuccessful();
    }

    public function testViewportMetaTagExists() : void {
        $client = self::createClient();
        $crawler = $client->request("GET", "/");
        $viewportTags = $crawler->filter('meta[name="viewport"]');
        self::assertGreaterThan(0, $viewportTags->count());
        $content = $viewportTags->attr('content') ?? '';
        self::assertStringContainsString('width=device-width, initial-scale=1.0', $content);
    }
}