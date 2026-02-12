<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Degree {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int|null $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'degree.name.not_blank')]
    #[Assert\Length(min: 3, max: 30, minMessage: 'degree.name.min_length', maxMessage: 'degree.name.max_length')]
    private string $name = '';

    #[ORM\Column]
    #[Assert\NotBlank(message: 'degree.description.not_blank')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'degree.description.min_length', maxMessage: 'degree.description.max_length')]
    private string $description = '';

    #[ORM\Column(unique: true)]
    private string $slug = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Course> */
    #[ORM\OneToMany(
        targetEntity: Course::class,
        mappedBy: 'degree',
    )]
    private Collection $courses;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->createdAt = new DatePoint();
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setId(int|null $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string 
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
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

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function initSlug(): self
    {
        $slug = new AsciiSlugger()
            ->slug($this->name)
            ->lower()
            ->toString();
        $this->slug = $slug;
        return $this;
    }
}