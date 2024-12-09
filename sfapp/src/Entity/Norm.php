<?php

namespace App\Entity;

use App\Repository\NormRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NormRepository::class)]
class Norm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $humidityMinNorm = null;

    #[ORM\Column]
    private ?int $humidityMaxNorm = null;

    #[ORM\Column]
    private ?int $temperatureMinNorm = null;

    #[ORM\Column]
    private ?int $temperatureMaxNorm = null;

    #[ORM\Column]
    private ?int $co2MinNorm = null;

    #[ORM\Column]
    private ?int $co2MaxNorm = null;

    #[ORM\Column(length: 255)]
    private ?string $season = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHumidityMinNorm(): ?int
    {
        return $this->humidityMinNorm;
    }

    public function setHumidityMinNorm(int $humidityMinNorm): static
    {
        $this->humidityMinNorm = $humidityMinNorm;

        return $this;
    }

    public function getHumidityMaxNorm(): ?int
    {
        return $this->humidityMaxNorm;
    }

    public function setHumidityMaxNorm(int $humidityMaxNorm): static
    {
        $this->humidityMaxNorm = $humidityMaxNorm;

        return $this;
    }

    public function getTemperatureMinNorm(): ?int
    {
        return $this->temperatureMinNorm;
    }

    public function setTemperatureMinNorm(int $temperatureMinNorm): static
    {
        $this->temperatureMinNorm = $temperatureMinNorm;

        return $this;
    }

    public function getTemperatureMaxNorm(): ?int
    {
        return $this->temperatureMaxNorm;
    }

    public function setTemperatureMaxNorm(int $temperatureMaxNorm): static
    {
        $this->temperatureMaxNorm = $temperatureMaxNorm;

        return $this;
    }

    public function getCo2MinNorm(): ?int
    {
        return $this->co2MinNorm;
    }

    public function setCo2MinNorm(int $co2MinNorm): static
    {
        $this->co2MinNorm = $co2MinNorm;

        return $this;
    }

    public function getCo2MaxNorm(): ?int
    {
        return $this->co2MaxNorm;
    }

    public function setCo2MaxNorm(int $co2MaxNorm): static
    {
        $this->co2MaxNorm = $co2MaxNorm;

        return $this;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(string $season): static
    {
        $this->season = $season;

        return $this;
    }
}
