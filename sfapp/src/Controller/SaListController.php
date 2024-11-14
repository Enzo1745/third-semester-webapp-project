<?php

namespace App\Controller;

use App\Entity\Sa;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\SaRepository;
use App\Repository\RoomRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SaListController extends AbstractController
{
    #[Route('/charge/gestion_sa', name: 'app_gestion_sa')]
    public function listSA(Request $request, SaRepository $saRepo): Response
    {
        $saList = $saRepo->findAll();

        return $this->render('gestion_sa/list.html.twig', [
            "saList" => $saList
        ]);
    }
}
