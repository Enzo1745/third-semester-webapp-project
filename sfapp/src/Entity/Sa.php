<?php

namespace App\Entity;

use App\Repository\Model\EtatSA;
use App\Repository\SaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaRepository::class)]
class Sa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: EtatSA::class)]
    private ?EtatSA $etat = null;

    #[ORM\OneToOne(inversedBy: 'sa', cascade: ['persist', 'remove'])]
    private ?Salle $salle = null;

    #[ORM\Column(nullable: true)]
    private ?int $Temperature = null;

    #[ORM\Column(nullable: true)]
    private ?int $Humidite = null;

    #[ORM\Column(nullable: true)]
    private ?int $CO2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?EtatSA
    {
        return $this->etat;
    }

    public function setEtat(EtatSA $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

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

    public function getHumidite(): ?int
    {
        return $this->Humidite;
    }

    public function setHumidite(int $Humidite): static
    {
        $this->Humidite = $Humidite;

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
