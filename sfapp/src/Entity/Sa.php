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
    private ?SaState $state = null;

    #[ORM\OneToOne(inversedBy: 'sa', cascade: ['persist', 'remove'])]
    private ?Room $room = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?SaState
    {
        return $this->state;
    }

    public function setState(SaState $state): static
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

        return $this;
    }
}
