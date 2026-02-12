<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    use ClockAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private null|int $id = null;

    #[ORM\Column(enumType: ArticleType::class, nullable: true)]
    private null|ArticleType $articleType = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'article.title.not_blank')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'article.title.min_length', maxMessage: 'article.title.max_length')]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'article.content.not_blank')]
    #[Assert\Length(min: 10, minMessage: 'article.content.min_length')]
    private string $content = '';

    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private null|\DateTimeImmutable $publishedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\Column(type: "text", nullable: true)]
    private null|string $uploadFilename = null;

    private null|File $uploadFile = null;

    public function __construct()
    {
        $this->createdAt = new DatePoint();
        $this->updatedAt = new DatePoint();
    }

    // Getter / Setter
    public function getId(): null|int
    {
        return $this->id;
    }
    public function setId(null|int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;

        if ($this->slug === '') {
            $this->slug = new AsciiSlugger()
                ->slug($title)
                ->lower()
                ->toString();
        }

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
    public function setSlug(string $slug): self
    {
        $this->slug = new AsciiSlugger()->slug($slug)->lower()->toString();
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }
    public function setPublished(bool $published): self
    {
        $this->published = $published;
        return $this;
    }
    public function publish(): self
    {
        $this->published = true;
        $this->publishedAt = $this->now();
        return $this;
    }
    public function unpublish(): self
    {
        $this->published = false;
        $this->publishedAt = null;
        return $this;
    }

    public function getPublishedAt(): null|\DateTimeImmutable
    {
        return $this->publishedAt;
    }
    public function setPublishedAt(null|\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(): self
    {
        $this->updatedAt = $this->now();
        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }
    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getArticleType(): null|ArticleType
    {
        return $this->articleType;
    }
    public function setArticleType(null|ArticleType $type): self
    {
        $this->articleType = $type;
        return $this;
    }

    public function getUploadFilename(): null|string
    {
        return $this->uploadFilename;
    }
    public function setUploadFilename(null|string $name): self
    {
        $this->uploadFilename = $name;
        return $this;
    }

    public function getUploadFile(): null|File
    {
        return $this->uploadFile;
    }
    public function setUploadFile(null|File $file): self
    {
        $this->uploadFile = $file;
        return $this;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;
        return $this;
    }
}
