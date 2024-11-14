<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RoomRepository;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $roomNumber = null;

    #[ORM\Column(nullable: true)]
    private ?int $idAS = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getRoomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(string $roomNumber): static
    {
        $this->roomNumber = $roomNumber;

        return $this;
    }

    public function getIdAS(): ?int
    {
        return $this->idAS;
    }

    public function setIdAS(?int $idAS): static
    {
        $this->idAS = $idAS;

        return $this;
    }
}