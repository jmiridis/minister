<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Uploadable\Uploadable;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

use App\Repository\TestimonialRepository;

#[Gedmo\Loggable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: TestimonialRepository::class)]
class Testimonial implements Uploadable
{
    use AuditTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\Versioned]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[Gedmo\Versioned]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Gedmo\Versioned]
    #[ORM\Column(nullable: true)]
    #[Gedmo\SortablePosition]
    private ?int $position = null;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private ?bool $isActive = null;

    #[Gedmo\Versioned]
    #[ORM\Column(name: 'signature', length: 255, nullable: true)]
    private ?string $name = null;

    #[Gedmo\Versioned]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $weddingDate = null;

    #[Gedmo\Versioned]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    public function __construct()
    {
        $this->isActive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getWeddingDate(): ?DateTime
    {
        return $this->weddingDate;
    }

    public function setWeddingDate(?DateTime $weddingDate): self
    {
        $this->weddingDate = $weddingDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    #[Vich\UploadableField(mapping: 'testimonial_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $updatedAt;

    // ...

    public function setImageFile(File $image = null): self
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new DateTime('now');
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
