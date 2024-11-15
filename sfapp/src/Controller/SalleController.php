<?php

namespace App\Controller;

use App\Form\SerchRoomASType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RoomRepository;

class SalleController extends AbstractController
{
    #[Route('/charge', name: 'app_charge')]
    public function index(): Response    {
        return $this->render('salle/index.html.twig', [
            ]);
    }

    #[Route('/charge/salles', name: 'app_salles')]
    public function rooms(): Response
    {
        return $this->render('salle/salles.html.twig', [
            ]);
    }

    #[Route('/charge/salles/liste', name: 'app_salle_liste')]
    public function listerSalles(RoomRepository $roomRepository, Request $request): Response
    {
        $form = $this->createForm( SerchRoomASType::class);
        $form->handleRequest($request);

        //getting the active filter
        $choice = $form->get('filter')->getData();

        // changing the content of the list depending on the selected filter
        if ($choice == 'RoomsWithAS') {
            $rooms = $roomRepository->findAllWithIdSA();
        }
        else if ($choice == 'RoomsWithoutAS') {
            $rooms = $roomRepository->findAllWithoutIdSA();
        }
        else{
            $rooms = $roomRepository->findAllOrderedByRoomNumber();
        }

        return $this->render('salle/liste_salles.html.twig', [
            'form' => $form->createView(),
            'rooms' => $rooms
        ]);
    }

    #[Route('/charge/salles/liste/{RoomNumber}', name: 'app_salle_info')]
    public function trouverInfosSalles(string $RoomNumber, RoomRepository $roomRepository): Response
    {
        $room = $roomRepository->findByRoomNumber($RoomNumber);

        return $this->render('salle/salle_info.html.twig', [
            'room' => $room
        ]);
    }
}