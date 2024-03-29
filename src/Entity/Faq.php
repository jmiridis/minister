<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use App\Repository\FaqRepository;

#[Gedmo\Loggable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: FaqRepository::class)]
class Faq
{
    use AuditTrait;
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\SortablePosition]
    #[Gedmo\Versioned]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $position = null;

    #[Gedmo\Versioned]
    #[ORM\Column]
    private ?bool $isActive = null;

    #[Gedmo\Versioned]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $question = null;

    #[Gedmo\Versioned]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}
