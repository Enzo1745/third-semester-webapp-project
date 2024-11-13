<?php

namespace App\Entity;

use App\Repository\Model\SaState;
use App\Repository\SaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaRepository::class)]
class Sa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: SaState::class)]
    private ?SaState $etat = null;

    #[ORM\OneToOne(inversedBy: 'sa', cascade: ['persist', 'remove'])]
    private ?Salle $salle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?SaState
    {
        return $this->etat;
    }

    public function setEtat(SaState $etat): static
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
}
