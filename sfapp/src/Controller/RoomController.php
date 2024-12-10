<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Sa;
use App\Form\AddRoomType;
use App\Form\SerchRoomASType;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use App\Repository\NormRepository;
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
    public function listRooms(RoomRepository $roomRepository, NormRepository $normRepository, Request $request, EntityManagerInterface $entityManager): Response
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

        $roomsWithDiagnostics = [];
        foreach ($rooms as $room) {
            $diagnosticStatus = $this->getDiagnosticStatus($room, $entityManager, $normRepository);
            $roomsWithDiagnostics[] = [
                'room' => $room,
                'diagnosticStatus' => $diagnosticStatus
            ];
        }

        // Render the list of rooms
        return $this->render('room/list_rooms.html.twig', [
            'form' => $form->createView(),
            'rooms' => $roomsWithDiagnostics,
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
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Trouver la salle par son nom
        $room = $roomRepository->findByRoomName($roomName);

        if (!$room) {
            return $this->render('room/not_found.html.twig', [
                'message' => 'Salle introuvable.',
            ]);
        }

        // Trouver le système d'acquisition associé, s'il existe
        $sa = null;
        if ($room && $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
        }



        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'origin' => 'charge'
        ]);
    }

    #[Route('/technicien/salles/{roomName}', name: 'app_room_info_technicien')]
    public function roomInfoTech(string $roomName, RoomRepository $roomRepository, EntityManagerInterface $entityManager): Response
    {
        // Find the room by its room numbe

        $room = $roomRepository->findByRoomName($roomName);


        if ($room && $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
        } else {
            $sa = null;
        }



        // Render the room information template
        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
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

        // Vérifiez si un SA est associé à la salle
        $sa = $room->getSa();
        if ($sa) {
            $sa->setRoom(null);
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
        }

        // Supprimez la salle
        $entityManager->remove($room);
        $entityManager->flush();

        $this->addFlash('success', 'Salle correctement supprimée');
        return $this->redirectToRoute('app_room_list');
    }

    public function getDiagnosticStatus(Room $room, EntityManagerInterface $entityManager, NormRepository $normRepository): string
    {
        $this->normRepository = $normRepository;

        // Fetch the norms for summer season
        $summerNorms = $this->normRepository->findOneBy(['season' => 'summer']);

        $sa = null;
        if ($room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
        }
        if (!$sa or $sa->getCO2() == null or $sa->getTemperature() == null or $sa->getHumidity() == null) {
            return 'grey';  // No functional SA found for the room, so no diagnostic
        }

        $temperatureCompliant = $sa->getTemperature() >= $summerNorms->getTemperatureMinNorm() &&
            $sa->getTemperature() <= $summerNorms->getTemperatureMaxNorm();
        $humidityCompliant = $sa->getHumidity() >= $summerNorms->getHumidityMinNorm() &&
            $sa->getHumidity() <= $summerNorms->getHumidityMaxNorm();
        $co2Compliant = $sa->getCO2() >= $summerNorms->getCo2MinNorm() &&
            $sa->getCO2() <= $summerNorms->getCo2MaxNorm();

        // Determine the diagnostic status
        $compliantCount = 0;
        if ($temperatureCompliant) {
            $compliantCount++;
        }
        if ($humidityCompliant) {
            $compliantCount++;
        }
        if ($co2Compliant) {
            $compliantCount++;
        }

        // Logic for diagnostic color
        if ($compliantCount === 3) {
            return 'green';
        } elseif ($compliantCount === 0) {
            return 'red';
        } else {
            return 'yellow';
        }
    }
}
