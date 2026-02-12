<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Article> */
final class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /** @return Article[] */
    public function findRecentArticles(int $max = 6): array
    {
        $qb = $this->createQueryBuilder('article');
        $qb->where($qb->expr()->eq('article.published', ':published'))
            ->setParameter('published', true)
            ->orderBy('article.publishedAt', 'DESC')
            ->setMaxResults($max);

        return $qb->getQuery()->getResult();
    }

    /** @return Article[] */
    public function findPublishedArticlesForCourse(Course $course): array
    {
        $qb = $this->createQueryBuilder('article');
        $qb->join('article.course', 'course')
        ->andWhere('article.published = :published')
        ->andWhere('course = :course')
        ->setParameter('published', true)
        ->setParameter('course', $course)
        ->orderBy('article.publishedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
