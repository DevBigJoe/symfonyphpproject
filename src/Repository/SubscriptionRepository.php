<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/** @extends ServiceEntityRepository<Subscription> */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * @return list<Subscription>
     */
    public function findByTopic(string $topic): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.topic = :topic')
            ->setParameter('topic', $topic)
            ->getQuery()
            ->getResult();
    }

    public function add(Subscription $subscription, bool $flush = false): void
    {
        $this->getEntityManager()->persist($subscription);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function isUserSubscribed(User $user, string $topic): bool
    {
        return (bool) $this->createQueryBuilder('s')
            ->select('s.id')
            ->where('s.user = :user')
            ->andWhere('s.topic = :topic')
            ->setParameter('user', $user)
            ->setParameter('topic', $topic)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
