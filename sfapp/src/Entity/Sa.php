<?php

namespace App\Entity;

use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaRepository::class)]
class
Sa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: SAState::class)]
    private ?SAState $state = null;

    #[ORM\OneToOne(inversedBy: 'sa', cascade: ['persist', 'remove'])]
    private ?Room $room = null;

    #[ORM\Column(nullable: true)]
    private ?int $Temperature = null;

    #[ORM\Column(nullable: true)]
    private ?int $Humidity = null;

    #[ORM\Column(nullable: true)]
    private ?int $CO2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?SAState
    {
        return $this->state;
    }

    public function getStateName(): string
    {
        return $this->state->value; // Retourne la valeur sous forme de chaîne
    }

    public function setState(SAState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        if ($room !== null && $room->getSa() !== $this) {
            $room->setSa($this);
        }

        return $this;
    }

    public function getTemperature(): ?int
    {
        return $this->Temperature;
    }

    public function setTemperature(int $Temperature): static
    {
        $this->Temperature = $Temperature;

        return $this;
    }

    public function getHumidity(): ?int
    {
        return $this->Humidity;
    }

    public function setHumidity(int $Humidity): static
    {
        $this->Humidity = $Humidity;

        return $this;
    }

    public function getCO2(): ?int
    {
        return $this->CO2;
    }

    public function setCO2(int $CO2): static
    {
        $this->CO2 = $CO2;

        return $this;
    }
}
