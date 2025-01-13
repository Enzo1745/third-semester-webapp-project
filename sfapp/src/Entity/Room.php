<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @brief the Room entity used to create rooms and link them to the SAs
 */
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

    #[ORM\Column]
    private ?int $NbRadiator = null;

    #[ORM\Column]
    private ?int $NbWindows = null;

    private ?string $diagnosticStatus = null;

    public function getDiagnosticStatus(): ?string
    {
        return $this->diagnosticStatus;
    }

    public function setDiagnosticStatus(?string $diagnosticStatus): self
    {
        $this->diagnosticStatus = $diagnosticStatus;
        return $this;
    }

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
            $this->sa = $sa;
            if ($sa->getId() !== null) {
                $this->idSa = $sa->getId();
            }
            if ($sa->getRoom() !== $this) {
                $sa->setRoom($this);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->roomName ?? 'Salle non définie';
    }

    public function getNbRadiator(): ?int
    {
        return $this->NbRadiator;
    }

    public function setNbRadiator(int $NbRadiator): static
    {
        $this->NbRadiator = $NbRadiator;

        return $this;
    }

    public function getNbWindows(): ?int
    {
        return $this->NbWindows;
    }

    public function setNbWindows(int $NbWindows): static
    {
        $this->NbWindows = $NbWindows;

        return $this;
    }
}