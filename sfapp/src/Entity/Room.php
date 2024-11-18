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

    #[ORM\Column(name: 'roomName',length: 255)]
    private ?string $roomName = null;

    #[ORM\OneToOne(mappedBy: 'room', cascade: ['persist', 'remove'])]
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

    public function getSa(): ?Sa
    {
        return $this->sa;
    }

    public function setSa(?Sa $sa): static
    {
        // unset the owning side of the relation if necessary
        if ($sa === null && $this->sa !== null) {
            $this->sa->setRoom(null);
        }

        // set the owning side of the relation if necessary
        if ($sa !== null && $sa->getRoom() !== $this) {
            $sa->setRoom($this);
        }

        $this->sa = $sa;

        return $this;
    }

    public function __toString(): string
    {
        return $this->roomName ?? 'Salle non définie';
    }
}
