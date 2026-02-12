<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Degree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Degree> */
final class DegreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Degree::class);
    }

    /** @return list<Degree> */
    public function findAll(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.slug', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): Degree|null
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}