<?php

namespace App\Entity;

use App\Repository\DownRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DownRepository::class)]
class Down
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column]
    private ?bool $temperature = null;

    #[ORM\Column]
    private ?bool $humidity = null;

    #[ORM\Column]
    private ?bool $CO2 = null;

    #[ORM\ManyToOne(inversedBy: 'Down')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sa $sa = null;

    #[ORM\Column]
    private ?bool $microcontroller = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function isTemperature(): ?bool
    {
        return $this->temperature;
    }

    public function setTemperature(bool $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function isHumidity(): ?bool
    {
        return $this->humidity;
    }

    public function setHumidity(bool $humidity): static
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function isCO2(): ?bool
    {
        return $this->CO2;
    }

    public function setCO2(bool $CO2): static
    {
        $this->CO2 = $CO2;

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

    public function isMicrocontroller(): ?bool
    {
        return $this->microcontroller;
    }

    public function setMicrocontroller(bool $microcontroller): static
    {
        $this->microcontroller = $microcontroller;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
