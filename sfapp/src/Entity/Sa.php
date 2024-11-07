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
}
