<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Sa;
use App\Form\AddRoomType;
use App\Form\SerchRoomASType;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use App\Repository\DownRepository;
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
                $this->addFlash('success', 'Salle ajouté avec succès');
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
     * Route: /charge/salles
     * Name: app_room_list
     * Description: Displays a list of all rooms.
     */
    #[Route('/charge/salles', name: 'app_room_list')]
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
     * Route: /charge/salles/{roomName}
     * Name: app_room_info
     * Description: Displays detailed information about a specific room.
     */
    #[Route('/charge/salles/{roomName}', name: 'app_room_info')]
    public function roomInfo(
        string $roomName,
        RoomRepository $roomRepository,
        DownRepository $downRepo,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Find a room by its name
        $room = $roomRepository->findByRoomName($roomName);
        $down = null;

        if (!$room) {
            return $this->render('room/not_found.html.twig', [
                'message' => 'Salle introuvable.',
            ]);
        }

        // Find the associated SA
        $sa = null;
        if ($room && $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
            if ($sa->getState() == SAState::Down)
            {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            } else {
                $sa = null;
            }
        }

        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'origin' => 'charge',
            'down' => $down
        ]);
    }

    #[Route('/technicien/salles/{roomName}', name: 'app_room_info_technicien')]
    public function roomInfoTech(string $roomName, RoomRepository $roomRepository, EntityManagerInterface $entityManager, DownRepository $downRepo): Response
    {
        // Find the room by its room number
        $room = $roomRepository->findByRoomName($roomName);
        $down = null;

        if ($room && $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
            if ($sa->getState() == SAState::Down)
            {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        } else {
            $sa = null;
        }

        // Render the room information template
        return $this->render('room/room_info.html.twig', [
            'room' => $room,
             'sa' => $sa,
            'down' => $down,
            'origin' => 'technicien'
        ]);
    }

    /**
     * Route: /charge/salles/supprimer/{id}
     * Name: app_room_delete
     * Methods: POST
     * Description: Deletes a room.
     */
    #[Route('/charge/salles/supprimer/{id}', name: 'app_room_delete', methods: ['POST'])]
    public function delete(?Room $room, EntityManagerInterface $entityManager): Response
    {
        if (!$room) {
            $this->addFlash('error', 'Salle introuvable.');
            return new Response('Salle introuvable.', Response::HTTP_NOT_FOUND);
            return $this->redirectToRoute('app_room_list');
        }

        // Verify that SA is asociated with a room
        $sa = $room->getSa();
        if ($sa) {
            $sa->setRoom(null);
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
        }
        // Delete the room from the database
        $entityManager->remove($room);
        $entityManager->flush();

        // Add a success flash message
        $this->addFlash('success', 'Salle correctement supprimée');

        // Redirect to the list of rooms
        return $this->redirectToRoute('app_room_list');
    }
}
