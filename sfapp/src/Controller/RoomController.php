<?php

namespace App\Controller;

use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Form\AddRoomType;
use App\Form\FilterTrierRoomsType;
use App\Form\SerchRoomASType;
use App\Repository\Model\NormSeason;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use App\Repository\NormRepository;
use App\Repository\DownRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\FilterTrier;
use App\Service\DiagnocticService;
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
     * Displays a list of rooms, filtered and/or sorted based on user's selection.
     */
    #[Route('/charge/salles', name: 'app_room_list')]
    public function listRooms(
        RoomRepository $roomRepository,
        DiagnocticService $diagnosticService,
        NormRepository $normRepository,
        Request $request
    ): Response {

        $form = $this->createForm(FilterTrierRoomsType::class);
        $form->handleRequest($request);

        // filter tri choice
        $filterChoice = $form->get('filter')->getData();
        $sortChoice   = $form->get('trier')->getData();

        // room filter
        $rooms = match ($filterChoice) {
            'withSA' => $roomRepository->findAllWithIdSa(),
            'withoutSA' => $roomRepository->findAllWithoutIdSa(),
            default => $roomRepository->findAllOrderedByRoomName(),
        };

        // summer norm
        $summerNorms = $normRepository->findOneBy([
            'NormType'   => 'confort',
            'NormSeason' => 'été'
        ]);

        // add diagnostic status to room
        foreach ($rooms as $room) {
            $sa = $room->getSa();
            $diagnosticColor = $diagnosticService->getDiagnosticStatus($sa, $room, $summerNorms);
            $room->setDiagnosticStatus($diagnosticColor);
        }

        //  trie
        if (in_array($sortChoice, ['Asso', 'DiaGood', 'DiaBad'])) {
            $choice = match ($sortChoice) {
                'Asso'    => 1,
                'DiaGood' => 2,
                'DiaBad'  => 3,
                default   => 0, // Valeur par défaut
            };

            $rooms = $roomRepository->sortRoomsByState($rooms, $choice);
        }

        // Préparer les données pour le template
        $roomsWithDiagnostics = array_map(function(Room $room) {
            return [
                'room'             => $room,
                'diagnosticStatus' => $room->getDiagnosticStatus(),
            ];
        }, $rooms);

        return $this->render('room/index.html.twig', [
            'form'  => $form->createView(),
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
        DownRepository $downRepo,
        EntityManagerInterface $entityManager
    ): Response {
        // Find a room by its name
        $room = $roomRepository->findByRoomName($roomName);
        $down = null;

        if (!$room) {
            return $this->render('room/not_found.html.twig', [
                'message' => 'Salle introuvable.',
            ]);
        }

        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => 'été'
        ]);

        // Find an SA if it exists
        $sa = null;
        if ($room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
            if ($sa && $sa->getState() == SAState::Down) {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        }



        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'origin' => 'charge',
            'norms' => $norms,
            'down' => $down,
        ]);
    }

    #[Route('/technicien/salles/{roomName}', name: 'app_room_info_technicien')]
    public function roomInfoTech(
        string $roomName,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        DownRepository $downRepo
    ): Response {
        // 1. Récupération de la salle par son nom
        $room = $roomRepository->findByRoomName($roomName);

        // 2. Initialisation des variables par défaut
        $sa = null;
        $down = null;
        $norms = null;

        // 3. Récupération des normes de type 'confort' pour la saison 'été'
        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => 'été'
        ]);

        // 4. Vérification de l'existence d'une SA associée à la salle
        if ($room && $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());

            // Si l'état de la SA est 'En panne', récupérer les détails de la panne
            if ($sa && $sa->getState() === SAState::Down) {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        }

        // 5. Passage des données au template
        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'down' => $down,
            'norms' => $norms, // Normes récupérées
            'origin' => 'technicien',
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
           // return $this->redirectToRoute('app_room_list');

        }

        // Verify that SA is asociated with a room
        $sa = $room->getSa();
        if ($sa) {
            $sa->setRoom(null);
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
        }

        // Delete the room
        $entityManager->remove($room);
        $entityManager->flush();

        // Add a success flash message
        $this->addFlash('success', 'Salle correctement supprimée');

        // Redirect to the list of rooms
        return $this->redirectToRoute('app_room_list');
    }

}
