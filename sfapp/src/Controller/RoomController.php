<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Sa;
use App\Form\AddRoomType;
use App\Form\SerchRoomASType;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoomController extends AbstractController
{
    /**
     * Route: /charge
     * Name: app_charge
     * Description: Displays the main index page.
     */
    #[Route('/charge', name: 'app_charge')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_room_list');
    }

    /**
     * Route: /charge/salles
     * Name: app_room
     * Description: Displays the main room management page.
     */
    #[Route('/charge/salles', name: 'app_room')]
    public function rooms(): Response
    {
        return $this->redirectToRoute('app_room_list');
    }

    /**
     * Route: /charge/salles/ajouter
     * Name: app_room_management
     * Description:
     *              Displays a form to add a room and processes form submissions.
     */
    #[Route('/charge/salles/ajouter', name: 'app_room_management')]
    public function manage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $newRoom = new Room();
        $formAddRoom = $this->createForm(AddRoomType::class, $newRoom);
        $formAddRoom->handleRequest($request);

        if ($formAddRoom->isSubmitted() && $formAddRoom->isValid()) {
            // Check if a room with the same room number already exists
            $existingRoom = $entityManager->getRepository(Room::class)
                ->findOneBy(['roomName' => $newRoom->getRoomName()]);

            if ($existingRoom) {
                // Add an error flash message
                $this->addFlash('danger', 'Cette salle existe déja');
            } else {
                // Save the new room to the database
                $entityManager->persist($newRoom);
                $entityManager->flush();
                // Add a success flash message
                $this->addFlash('success', 'Salle ajouté avec succes');
            }
            // Redirect back to the room management page
            return $this->redirectToRoute('app_room_management');
        }

        // Render the room management template with the form
        return $this->render('room/roomManagement.html.twig', [
            'formAddRoom' => $formAddRoom->createView(),
        ]);
    }

    /**
     * Route: /charge/salles/liste
     * Name: app_room_list
     * Description: Displays a list of all rooms.
     */
    #[Route('/charge/salles/liste', name: 'app_room_list')]
    public function listRooms(RoomRepository $roomRepository,Request $request): Response
    {
        // Fetch all rooms ordered by room number

        $form = $this->createForm( SerchRoomASType::class);
        $form->handleRequest($request);

        //getting the active filter
        $choice = $form->get('filter')->getData();

        // changing the content of the list depending on the selected filter
        if ($choice === 'RoomsWithAS') {
            $rooms = $roomRepository->findAllWithIdSa();
        }
        else if ($choice === 'RoomsWithoutAS') {
            $rooms = $roomRepository->findAllWithoutIdSa();
        }
        else{
            $rooms = $roomRepository->findAllOrderedByRoomNumber();
        }



        // Render the list of rooms
        return $this->render('room/list_rooms.html.twig', [
            'form' => $form->createView(),
            'rooms' => $rooms
        ]);
    }

    /**
     * Route: /charge/salles/liste/{roomName}
     * Name: app_room_info
     * Description: Displays detailed information about a specific room.
     */
    #[Route('/charge/salles/liste/{roomName}', name: 'app_room_info')]
    public function roomInfo(string $roomName, RoomRepository $roomRepository, EntityManagerInterface $entityManager): Response
    {
        // Find the room by its room numbe

        $room = $roomRepository->findByRoomName($roomName);

        if ($room && $room->getIdSA()) {
            // Récupérer l'objet complet Sa à partir de l'EntityManager
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
        } else {
            $sa = null;
        }

        // Render the room information template
        return $this->render('room/room_info.html.twig', [
            'room' => $room,
             'sa' => $sa
        ]);
    }

    /**
     * Route: /charge/salles/supprimer/{id}
     * Name: app_room_delete
     * Methods: POST
     * Description: Deletes a room.
     */
    #[Route('/charge/salles/supprimer/{id}', name: 'app_room_delete', methods: ['POST'])]
    public function delete(Room $room, EntityManagerInterface $entityManager): Response
    {

        //dossociate sa and room
        if ($sa = $room->getSa()) {
            $sa->setRoom(null);
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
        }
        // Delete the room from the database
        $entityManager->remove($room);
        $entityManager->flush();

        // Add a success flash message
        $this->addFlash('success', 'Salle correctement supprime');

        // Redirect to the list of rooms
        return $this->redirectToRoute('app_room_list');
    }
}
