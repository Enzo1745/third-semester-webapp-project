<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'roomName', length: 255)]
    private ?string $roomName = null;

    #[ORM\Column(name: 'idSa', nullable: true)]
    private ?int $idSa = null;

    #[ORM\OneToOne(mappedBy: 'room', cascade: ['persist'])]
    private ?Sa $sa = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function setRoomName(string $roomName): static
    {
        $this->roomName = $roomName;
        return $this;
    }

    public function getIdSa(): ?int
    {
        return $this->idSa;
    }

    public function setIdSa(?int $idSa): static
    {
        $this->idSa = $idSa;
        return $this;
    }

    public function getSa(): ?Sa
    {
        return $this->sa;
    }

    public function setSa(?Sa $sa): static
    {
        if ($sa === null) {
            if ($this->sa !== null) {
                $this->sa->setRoom(null);
            }
            $this->idSa = null;
        } else {
            $this->idSa = $sa->getId();
            if ($sa->getRoom() !== $this) {
                $sa->setRoom($this);
            }
        }
            $this->sa = $sa;
            return $this;

    }

    public function __toString(): string
    {
        return $this->roomName ?? 'Salle non définie';
    }
}