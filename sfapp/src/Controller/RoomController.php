<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RoomRepository;
use App\Repository\AsRepository;

class RoomController extends AbstractController
{
    /**
     * Route: /charge
     * Name: app_charge
     * Description: Displays the main index page.
     */
    #[Route('/charge', name: 'app_charge')]
    public function index(): Response    {
        return $this->render('salle/index.html.twig', [
            ]);
    }

    /**
     * Route: /charge/salles
     * Name: app_room_list
     * Description: Displays a list of all rooms.
     */
    #[Route('/charge/salles', name: 'app_salle_liste')]
    public function listRooms(RoomRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAllOrderedByNumSalle();

        return $this->render('salle/liste_salles.html.twig', [
            'salles' => $salles
        ]);
    }


    /**
     * Route: /charge/salles/{roomNumber}
     * Name: app_room_info
     * Description: Displays detailed information about a specific room.
     */
    #[Route('/charge/salles/{NumSalle}', name: 'app_salle_info')]
    public function trouverInfosSalles(string $NumSalle, RoomRepository $salleRepository, AsRepository $saRepository): Response
    {
        $salle = $salleRepository->findByNumSalle($NumSalle);
        $sa = $salle->getIdSA();

        return $this->render('salle/salle_info.html.twig', [
            'salle' => $salle,
            'sa' => $sa
        ]);
    }
}