<?php
namespace App\Repository\Model;

enum UserRoles: string {
    case Charge = "Chargé";
    case Technicien = "Technicien";
    case Utilisateur = "Utilisateur";
}