<?php

namespace App\Entity;

use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @brief the Sa entity used to create the SAs and link them to the rooms
 */
#[ORM\Entity(repositoryClass: SaRepository::class)]
class Sa
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(enumType: SAState::class)]
    private ?SAState $state = null;

    #[ORM\OneToOne(inversedBy: 'sa', cascade: ['persist'])]
    private ?Room $room = null;

    #[ORM\Column(nullable: true)]
    private ?int $Temperature = null;

    #[ORM\Column(nullable: true)]
    private ?int $Humidity = null;

    #[ORM\Column(nullable: true)]
    private ?int $CO2 = null;

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

    /**
     * @var Collection<int, Down>
     */
    #[ORM\OneToMany(targetEntity: Down::class, mappedBy: 'sa')]
    private Collection $Down;

    /**
     * @var Collection<int, Measure>
     */
    #[ORM\OneToMany(targetEntity: Measure::class, mappedBy: 'sa')]
    private Collection $measures;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $lum = null;

    #[ORM\Column(nullable: true)]
    private ?bool $pres = null;

    public function __construct()
    {
        $this->Down = new ArrayCollection();
        $this->measures = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getState(): ?SAState
    {
        return $this->state;
    }

    public function getStateName(): string
    {
        return $this->state->value;
    }

    public function setState(SAState $state): static
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

        if ($room !== null && $room->getSa() !== $this) {
            $room->setSa($this);
        }

        return $this;
    }

    public function getTemperature(): ?int
    {
        return $this->Temperature;
    }

    public function setTemperature(?int $Temperature): static
    {
        $this->Temperature = $Temperature;

        return $this;
    }

    public function getHumidity(): ?int
    {
        return $this->Humidity;
    }

    public function setHumidity(?int $Humidity): static
    {
        $this->Humidity = $Humidity;

        return $this;
    }

    public function getCO2(): ?int
    {
        return $this->CO2;
    }

    public function setCO2(?int $CO2): static
    {
        $this->CO2 = $CO2;

        return $this;
    }

    /**
     * @return Collection<int, Down>
     */
    public function getDown(): Collection
    {
        return $this->Down;
    }

    public function addDown(Down $down): static
    {
        if (!$this->Down->contains($down)) {
            $this->Down->add($down);
            $down->setSa($this);
        }
        return $this;
    }

    public function removeDown(Down $down): static
    {
        if ($this->Down->removeElement($down)) {
            // set the owning side to null (unless already changed)
            if ($down->getSa() === $this) {
                $down->setSa(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Measure>
     */
    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function addMeasure(Measure $measure): static
    {
        if (!$this->measures->contains($measure)) {
            $this->measures->add($measure);
            $measure->setSa($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): static
    {
        if ($this->measures->removeElement($measure)) {
            // set the owning side to null (unless already changed)
            if ($measure->getSa() === $this) {
                $measure->setSa(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLum(): ?int
    {
        return $this->lum;
    }

    public function setLum(?int $lum): static
    {
        $this->lum = $lum;

        return $this;
    }

    public function isPres(): ?bool
    {
        return $this->pres;
    }

    public function setPres(?bool $pres): static
    {
        $this->pres = $pres;

        return $this;
    }
}
