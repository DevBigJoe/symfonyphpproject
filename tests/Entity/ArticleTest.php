<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Article;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

final class ArticleTest extends TestCase
{
    use ClockSensitiveTrait;

    #[TestWith(['German umlaute - äüöß', 'german-umlaute-auoss'])]
    #[TestWith(['A single headline!', 'a-single-headline'])]
    #[TestWith(['UPPER -- lower', 'upper-lower'])]
    public function testSlugifyTitle(string $title, string $expectedSlug): void
    {
        $article = new Article()
            ->setTitle($title);

        self::assertSame($expectedSlug, $article->getSlug());
    }

    #[TestWith(['German umlaute - äüöß'])]
    #[TestWith(['A single headline!'])]
    #[TestWith(['UPPER -- lower'])]
    public function testSkipSlugify(string $title): void
    {
        $article = new Article()
            ->setSlug('some-static-slug')
            ->setTitle($title);

        self::assertSame('some-static-slug', $article->getSlug());
    }

    public function testUnPublishArticle(): void
    {
        $article = new Article()
            ->setPublished(true)
            ->setPublishedAt(new DatePoint('2025-10-01 13:11'));

        $article->unpublish();

        self::assertFalse($article->isPublished());
        self::assertNull($article->getPublishedAt());
    }

    public function testPublishArticle(): void
    {
        $article = new Article();

        self::mockTime('2025-10-03 11:11');
        $article->publish();

        self::assertTrue($article->isPublished());
        self::assertNotNull($article->getPublishedAt());
        self::assertSame('2025-10-03 11:11', $article->getPublishedAt()->format('Y-m-d H:i'));
    }

    public function testRePublishArticle(): void
    {
        $article = new Article();
        self::mockTime('2025-09-03 11:11');
        $article->publish();

        self::mockTime('2025-10-03 11:11');
        $article->publish();

        self::assertTrue($article->isPublished());
        self::assertNotNull($article->getPublishedAt());
        self::assertSame('2025-10-03 11:11', $article->getPublishedAt()->format('Y-m-d H:i'));
    }
}
