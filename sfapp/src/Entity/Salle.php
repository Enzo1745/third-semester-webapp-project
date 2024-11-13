<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SalleRepository;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $NumSalle = null;

    #[ORM\OneToOne(targetEntity: Sa::class, inversedBy: 'salle')]
    #[ORM\JoinColumn(name: 'id_sa', referencedColumnName: 'id', nullable: true)]
    private ?Sa $IdSA = null;

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

    public function getIdSA(): ?Sa
    {
        return $this->IdSA;
    }

    public function setIdSA(?Sa $IdSA): static
    {
        $this->IdSA = $IdSA;

        return $this;
    }
}
