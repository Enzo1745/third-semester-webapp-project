<?php
namespace App\Repository\Model;

enum SAState: string
{
    case Functional = "Fonctionnel";
    case Breakdown = "En panne";
    case Available = "Disponible";
    case Waiting = "En attente";


}