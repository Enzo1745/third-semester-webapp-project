<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SalleController extends AbstractController
{
    #[Route('/charge', name: 'app_charge')]
    public function index(): Response    {
        return $this->render('salle/index.html.twig', [
            ]);
    }

    #[Route('/salles', name: 'app_salles')]
    public function salles(): Response
    {
        return $this->render('salle/salles.html.twig', [
            ]);
    }

    #[Route('/salles/liste', name: 'app_salle_liste')]
    public function liste(): Response
    {
        return $this->render('salle/liste_salles.html.twig', [
        ]);
    }
//
//    #[Route('/salles/liste/{$id}', name: 'app_salle_liste')]
//    public function salle_info(): Response
//    {
//        return $this->render('salle/salle_info.html.twig', [
//        ]);
//    }
}