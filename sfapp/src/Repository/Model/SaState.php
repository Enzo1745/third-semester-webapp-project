<?php
namespace App\Repository\Model;

enum SaState: string {
    case Functional = "Fonctionnel";
    case Breakdown = "En panne";
    case Available = "Disponible";
}