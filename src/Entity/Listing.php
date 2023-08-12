<?php

namespace App\Entity;

use App\Enum\ListingStatusEnum;
use App\Repository\ListingRepository;
use App\Traits\SlugTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Listing
{
    use SlugTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = ListingStatusEnum::NOT_VERIFIED;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $edited_at = null;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $belongs_to_user = null;

    #[ORM\Column]
    private ?int $views = 0;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?Category $category = null;


    #[ORM\PrePersist]
    public function setInitialValues(): void
    {
        $this->slug = $this->createSlug($this->title);
        $this->created_at = new \DateTime();
        $this->edited_at = null;
    }

    #[ORM\PreUpdate]
    public function setUpdatedValues(PreUpdateEventArgs $args): void
    {
        $changes = $args->getEntityChangeSet();

        if(isset($changes['title'])) {
            $this->slug = $this->createSlug($this->title);
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->edited_at;
    }

    public function setEditedAt(?\DateTimeInterface $edited_at): static
    {
        $this->edited_at = $edited_at;

        return $this;
    }

    public function getBelongsToUser(): ?User
    {
        return $this->belongs_to_user;
    }

    public function setBelongsToUser(?User $belongs_to_user): static
    {
        $this->belongs_to_user = $belongs_to_user;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function incrementViews(): static
    {
        $this->views++;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }



}
