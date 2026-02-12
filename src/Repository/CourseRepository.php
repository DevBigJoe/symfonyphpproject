<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Course> */
final class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /** @return Course[] */
    public function findByDegreeSlug(string $degreeSlug): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.degree', 'd')
            ->andWhere('d.slug = :degreeSlug')
            ->setParameter('degreeSlug', $degreeSlug)
            ->orderBy('c.slug', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): Course|null
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}