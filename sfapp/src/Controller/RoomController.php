<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\AddRoomType;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoomController extends AbstractController
{
    #[Route('/charge', name: 'app_charge')]
    public function index(): Response
    {
        return $this->render('index.html.twig', []);
    }

    #[Route('/charge/salles', name: 'app_room')]
    public function rooms(): Response
    {
        return $this->render('room/rooms.html.twig', []);
    }

    #[Route('/charge/salles/ajouter', name: 'app_room_management')]
    public function manage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $newRoom = new Room();
        $formAddRoom = $this->createForm(AddRoomType::class, $newRoom);
        $formAddRoom->handleRequest($request);

        if ($formAddRoom->isSubmitted() && $formAddRoom->isValid()) {
            $existingRoom = $entityManager->getRepository(Room::class)
                ->findOneBy(['roomNumber' => $newRoom->getRoomNumber()]);

            if ($existingRoom) {
                $this->addFlash('error', 'Cette salle existe déja');
            } else {
                $entityManager->persist($newRoom);
                $entityManager->flush();
                $this->addFlash('success', 'Ajout réussi de la salle');
            }
            return $this->redirectToRoute('app_room_management');
        }

        return $this->render('room/roomManagement.html.twig', [
            'formAddRoom' => $formAddRoom->createView(),
        ]);
    }

    #[Route('/charge/salles/liste', name: 'app_room_list')]
    public function listRooms(RoomRepository $roomRepository): Response
    {
        $rooms = $roomRepository->findAllOrderedByRoomNumber();

        return $this->render('room/list_rooms.html.twig', [
            'rooms' => $rooms
        ]);
    }

    #[Route('/charge/salles/liste/{roomNumber}', name: 'app_room_info')]
    public function roomInfo(string $roomNumber, RoomRepository $roomRepository): Response
    {
        $room = $roomRepository->findByRoomNumber($roomNumber);

        return $this->render('room/room_info.html.twig', [
            'room' => $room
        ]);
    }


    #[Route('/charge/salles/supprimer/{id}', name: 'app_room_delete', methods: ['POST'])]
    public function delete(Room $room, EntityManagerInterface $entityManager): Response
    {
        // Supprime la salle
        $entityManager->remove($room);
        $entityManager->flush();

        // Ajoute un message flash de succès
        $this->addFlash('success', 'La salle a été supprimée avec succès.');

        // Redirige vers la liste des salles
        return $this->redirectToRoute('app_room_list');
    }

}