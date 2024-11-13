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

    #[Route('/charge/salles', name: 'app_salles')]
    public function salles(): Response
    {
        return $this->render('salle/salles.html.twig', [
            ]);
    }

    #[Route('/charge/salles/liste', name: 'app_salle_liste')]
    public function liste(): Response
    {
        return $this->render('salle/liste_salles.html.twig', [
        ]);
    }

    #[Route('/charge/salles/liste/{id}', name: 'app_salle_info')]
    public function salle_info(int $id): Response
    {
        return $this->render('salle/salle_info.html.twig', [
            'id' => $id
        ]);
    }
}