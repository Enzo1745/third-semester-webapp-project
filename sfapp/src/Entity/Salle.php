<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'nomSalle',length: 255)]
    private ?string $nomSalle = null;

    #[ORM\OneToOne(mappedBy: 'salle', cascade: ['persist', 'remove'])]
    private ?Sa $sa = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalle(): ?string
    {
        return $this->nomSalle;
    }

    public function setNomSalle(string $nomSalle): static
    {
        $this->nomSalle = $nomSalle;

        return $this;
    }

    public function getSa(): ?Sa
    {
        return $this->sa;
    }

    public function setSa(?Sa $sa): static
    {
        // unset the owning side of the relation if necessary
        if ($sa === null && $this->sa !== null) {
            $this->sa->setSalle(null);
        }

        // set the owning side of the relation if necessary
        if ($sa !== null && $sa->getSalle() !== $this) {
            $sa->setSalle($this);
        }

        $this->sa = $sa;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nomSalle ?? 'Salle non définie';
    }
}
