<?php

namespace App\Entity;

use App\Repository\ComfortInstructionRoomRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComfortInstructionRoomRepository::class)]
class ComfortInstructionRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Room::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne(targetEntity: ComfortInstruction::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ComfortInstruction $comfortInstruction = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $DoneByUserDate = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getComfortInstruction(): ?ComfortInstruction
    {
        return $this->comfortInstruction;
    }

    public function setComfortInstruction(?ComfortInstruction $comfortInstruction): static
    {
        $this->comfortInstruction = $comfortInstruction;
        return $this;
    }

    public function getDoneByUserDate(): ?\DateTimeInterface
    {
        return $this->DoneByUserDate;
    }

    public function setDoneByUserDate(?\DateTimeInterface $DoneByUserDate): static
    {
        $this->DoneByUserDate = $DoneByUserDate;

        return $this;
    }
}
