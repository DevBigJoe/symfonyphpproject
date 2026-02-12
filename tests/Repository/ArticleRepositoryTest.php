<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use App\Entity\User;

final class ArticleRepositoryTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    public function testFindRecentArticles(): void
    {
        self::bootKernel();

        [$firstId, $secondId, $thirdId] = $this->createRecentArticles();

        $repository = $this->getEntityManager()->getRepository(Article::class);
        $articles   = $repository->findRecentArticles();
        self::assertCount(3, $articles);
        self::assertSame($firstId, $articles[0]->getId());
        self::assertSame($secondId, $articles[1]->getId());
        self::assertSame($thirdId, $articles[2]->getId());
    }

    /** @return int[] */
    private function createRecentArticles(): array
    {
        $em = $this->getEntityManager();

        // Test-Degree erstellen
        $degree = new \App\Entity\Degree();
        $degree->setName('Test Degree');
        $degree->setDescription('Test Description');
        $degree->initSlug();
        $em->persist($degree);

        // Test-Course erstellen
        $course = new \App\Entity\Course();
        $course->setName('Test Course');
        $course->setDescription('Test Description');
        $course->setDegree($degree);
        $course->initSlug();
        $em->persist($course);

        $user = new User('user@test.de')
            ->setName('User')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test');

        $em->persist($user);

        // Test-Artikel erstellen
        self::mockTime('2025-06-01');
        $em->persist(
            $second = new Article()
                ->setTitle('Some interesting stuff.')
                ->setContent('Content for a news item.')
                ->publish()
                ->setAuthor($user)
                ->setCourse($course),
        );

        self::mockTime('2025-10-01');
        $em->persist(
            $first = new Article()
                ->setTitle('Some interesting stuff1.')
                ->setContent('Content for a news item.')
                ->publish()
                ->setAuthor($user)
                ->setCourse($course),
        );

        self::mockTime('2025-04-01');
        $em->persist(
            $third = new Article()
                ->setTitle('Some interesting stuff2.')
                ->setContent('Content for a news item.')
                ->publish()
                ->setAuthor($user)
                ->setCourse($course),
        );

        $em->flush();
        $em->clear();

        //$ids = [$first->getId(), $second->getId(), $third->getId()];

        //Hinzugefügt am 27.11: Update von Claudia Schmidt
        return [
            $first->getId() ?? throw new \RuntimeException(),
            $second->getId() ?? throw new \RuntimeException(),
            $third->getId() ?? throw new \RuntimeException(),
        ];
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
