<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Repository\UserRepository;
use App\Entity\Article;
use App\Entity\Enum\ArticleType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\Component\HttpKernel\KernelInterface;
/**
 * @phpstan-type ArticleData array{
 *     title: string,
 *     content: string,
 *     author_email: string,
 *     course_slug: string,
 *     article_type: string,
 *     created_at: string,
 *     published_at: string,
 *     upload_filename?: string
 * }
 */

final class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    private string $uploadsDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->uploadsDir = $kernel->getProjectDir() . '/public/uploads/articles';
    }

    public function load(ObjectManager $manager): void
    {

        /** @var UserRepository $userRepository */
        $userRepository = $manager->getRepository(User::class);
        /** @var CourseRepository $courseRepository */
        $courseRepository = $manager->getRepository(Course::class);
        $articles = $this->getData();

        foreach ($articles as $articleData) {
            $user = $userRepository->findOneByEmail($articleData['author_email']);
            $course = $courseRepository->findOneBySlug($articleData['course_slug']);
            if (!$user) {
                throw new \RuntimeException("User not found");
            }
            if (!$course) {
                throw new \RuntimeException("Course not found");
            }
            $article = new Article()
                ->setTitle($articleData['title'])
                ->setContent($articleData['content'])
                ->setAuthor($user)
                ->setArticleType(ArticleType::tryFrom($articleData['article_type']))
                ->setCreatedAt(DatePoint::createFromFormat('Y-m-d\TH:i:s\Z', $articleData['created_at']))
                ->publish()
                ->setPublishedAt(DatePoint::createFromFormat('Y-m-d\TH:i:s\Z', $articleData['published_at']))
                ->setCourse($course);

            if (isset($articleData['upload_filename']) && $articleData['upload_filename'] !== '') {
                $filesystem = new Filesystem();
                $filesystem->mkdir($this->uploadsDir);
                $filename = $articleData['upload_filename'];
                $filePath = $this->uploadsDir . '/' . $filename;
                $filesystem->dumpFile($filePath, "");
                $article->setUploadFilename($filename);
            }

            $manager->persist($article);
        }

        $manager->flush();
    }
     
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CourseFixtures::class,
        ];
    }

    /**
     * @return ArticleData[]
     */
    public function getData(): array
    {
        $data = new Filesystem()->readFile(__DIR__ . '/data/articles.json');

        $decodedData = json_decode($data, true, flags: \JSON_THROW_ON_ERROR);
        \assert(\is_array($decodedData));

        return array_values($decodedData);
    }
}
