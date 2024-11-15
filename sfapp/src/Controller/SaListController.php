<?php

namespace App\Controller;

use App\Entity\Sa;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\SaRepository;
use App\Repository\RoomRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @brief Controller of the list of sa
 */
class SaListController extends AbstractController
{
    /**
     * @brief Function to return the list of sa in the web page
     * @param Request $request
     * @param SaRepository $saRepo
     * @return Response -> return the web page with the list of all sa
     */
    #[Route('/charge/gestion_sa', name: 'app_gestion_sa')]
    public function listSA(Request $request, SaRepository $saRepo): Response
    {
        $saList = $saRepo->findAll(); // Function used to return all the sa in the database.

        return $this->render('gestion_sa/list.html.twig', [
            "saList" => $saList
        ]);
    }
}
