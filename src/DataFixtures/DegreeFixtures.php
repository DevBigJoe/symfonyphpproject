<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Degree;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @phpstan-type DegreeData array{
 *     name: string,
 *     description: string
 * }
 */

final class DegreeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $degrees = $this->getData();
        foreach ($degrees as $degreeData) {
            $degree = new Degree()
                ->setName($degreeData['name'])
                ->setDescription($degreeData['description'])
                ->initSlug();
            $manager->persist($degree);
        }
        $manager->flush();
    }

    /**
     * @return DegreeData[]
     */
    public function getData(): array
    {
        $data = new Filesystem()->readFile(__DIR__ . '/data/degrees.json');
        $decodedData = json_decode($data, true, flags: \JSON_THROW_ON_ERROR);
        \assert(\is_array($decodedData));
        return array_values($decodedData);
    }
}