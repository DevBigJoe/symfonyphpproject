<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @phpstan-type UserData array{
 *     email: string,
 *     name: string,
 *     roles: string[],
 *     password: string,
 *     isVerified: bool,
 *     lastLogin: string,
 *     verificationToken: string|null
 * }
 */

final class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = $this->getData();
        print "hello work";
        foreach ($users as $userData) {
            $user = new User($userData['email'])
                ->setName($userData['name'])
                ->setRoles($userData['roles'])
                ->setPassword($userData['password'])
                ->setIsVerified($userData['isVerified'])
                ->setLastLogin(
                    $userData['lastLogin'] === null ? null : 
                    new DatePoint($userData['lastLogin'])
                )
                ->setVerificationToken($userData['verificationToken'] ?? null);
            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @return UserData[]
     */
    public function getData(): array
    {
        print "hello";
        $data = new Filesystem()->readFile(__DIR__ . '/data/users.json');
        print "yes";
        $decodedData = json_decode($data, true, flags: \JSON_THROW_ON_ERROR);
        print "lol";
        \assert(\is_array($decodedData));

        return array_values($decodedData);
    }
}