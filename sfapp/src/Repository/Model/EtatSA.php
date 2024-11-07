<?php
namespace App\Repository\Model;

enum EtatSA: string {
    case Fonctionnel = "Fonctionnel";
    case Panne = "En panne";
    case Dispo = "Disponible";
}