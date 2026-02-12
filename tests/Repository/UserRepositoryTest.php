<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(UserRepository::class);
    }

    public function testFindAllReturnsUsersOrderedById(): void
    {
        $user1 = new User('first@test.de')
            ->setName('First User')
            ->setPassword('password123');
        $user2 = new User('second@test.de')
            ->setName('Second User')
            ->setPassword('password123');
        $user3 = new User('third@test.de')
            ->setName('Third User')
            ->setPassword('password123');

        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->persist($user3);
        $this->em->flush();

        $users = $this->repository->findAll();

        self::assertCount(3, $users);
        self::assertSame($user1->getId(), $users[0]->getId());
        self::assertSame($user2->getId(), $users[1]->getId());
        self::assertSame($user3->getId(), $users[2]->getId());
    }

    public function testFindAllReturnsEmptyArrayWhenNoUsers(): void
    {
        $users = $this->repository->findAll();

        self::assertIsArray($users);
        self::assertCount(0, $users);
    }

    public function testFindOneByEmailReturnsUser(): void
    {
        $user = new User('findme@test.de')
            ->setName('Find Me')
            ->setPassword('password123');

        $this->em->persist($user);
        $this->em->flush();

        $foundUser = $this->repository->findOneByEmail('findme@test.de');

        self::assertNotNull($foundUser);
        self::assertSame($user->getId(), $foundUser->getId());
        self::assertSame('findme@test.de', $foundUser->getEmail());
    }

    public function testFindOneByEmailIsCaseInsensitive(): void
    {
        $user = new User('lowercase@test.de')
            ->setName('Lowercase User')
            ->setPassword('password123');

        $this->em->persist($user);
        $this->em->flush();

        $foundUser = $this->repository->findOneByEmail('LOWERCASE@TEST.DE');

        self::assertNotNull($foundUser);
        self::assertSame($user->getId(), $foundUser->getId());
    }

    public function testFindOneByEmailReturnsNullWhenNotFound(): void
    {
        $foundUser = $this->repository->findOneByEmail('nonexistent@test.de');

        self::assertNull($foundUser);
    }
}
