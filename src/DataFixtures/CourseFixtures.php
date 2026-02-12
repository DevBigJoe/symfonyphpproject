<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Degree;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Repository\DegreeRepository;

/**
 * @phpstan-type CourseData array{
 *     name: string,
 *     description: string,
 *     degree_slug: string
 * }
 */

final class CourseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var DegreeRepository $degreeRepository */
        $degreeRepository = $manager->getRepository(Degree::class);
        $courses = $this->getData();
        foreach ($courses as $courseData) {
            $degree = $degreeRepository->findOneBySlug($courseData['degree_slug']);
            if (!$degree) {
                throw new \RuntimeException("Degree not found");
            }
            $course = new Course();
            $course->setName($courseData['name']);
            $course->setDescription($courseData['description']);
            $course->setDegree($degree);
            $course->initSlug();
            $manager->persist($course);
        }

        $manager->flush();
    }
     
    public function getDependencies(): array
    {
        return [
            DegreeFixtures::class,
        ];
    }



    /**
     * @return CourseData[]
     */
    public function getData(): array
    {
        $data = new Filesystem()->readFile(__DIR__ . '/data/courses.json');
        $decodedData = json_decode($data, true, flags: \JSON_THROW_ON_ERROR);
        \assert(\is_array($decodedData));
        return array_values($decodedData);
    }
}