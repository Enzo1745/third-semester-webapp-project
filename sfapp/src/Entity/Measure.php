<?php

namespace App\Entity;

use App\Repository\MeasureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Sa;

/**
 * @brief the mesure entity used to store data from the API
 */
#[ORM\Entity(repositoryClass: MeasureRepository::class)]
class Measure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $value = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $captureDate = null;

    #[ORM\ManyToOne(inversedBy: 'measures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sa $sa = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function __construct(int $id, float $valeur, string $type, \DateTime $dateCapture, string $description, Sa $sa)
    {
        $this->value = $valeur;
        $this->type = $type;
        $this->captureDate = $dateCapture;
        $this->description = $description;
        $this->sa = $sa;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(?float $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCaptureDate(): ?\DateTimeInterface
    {
        return $this->captureDate;
    }

    public function setCaptureDate(\DateTimeInterface $captureDate): static
    {
        $this->captureDate = $captureDate;

        return $this;
    }

    public function getSa(): ?Sa
    {
        return $this->sa;
    }

    public function setSa(?Sa $sa): static
    {
        $this->sa = $sa;

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
}
