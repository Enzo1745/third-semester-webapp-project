<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Id = null;

    #[ORM\Column(length: 255)]
    private ?string $NumSalle = null;

    #[ORM\Column(nullable: true)]
    private ?int $IdSA = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $Id): static
    {
        $this->Id = $Id;

        return $this;
    }

    public function getNumSalle(): ?string
    {
        return $this->NumSalle;
    }

    public function setNumSalle(string $NumSalle): static
    {
        $this->NumSalle = $NumSalle;

        return $this;
    }

    public function getIdSA(): ?int
    {
        return $this->IdSA;
    }

    public function setIdSA(?int $IdSA): static
    {
        $this->IdSA = $IdSA;

        return $this;
    }
}
