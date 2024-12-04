<?php

namespace App\Entity;

use App\Repository\Model\UserRoles;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;

// the user table
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;
    #[ORM\Column(type: "string", enumType: UserRoles::class)]
    private ?UserRoles $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?UserRoles
    {
        return $this->role;
    }

    public function setRole(UserRoles $role): static
    {
        $this->role = $role;

        return $this;
    }
}