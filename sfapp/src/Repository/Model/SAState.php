<?php
namespace App\Repository\Model;

enum SAState: string {
    case Installed = "Installé";
    case Down = "En panne";
    case Available = "Disponible";
    case Waiting = "En attente";
}