<?php

namespace App\Controller;

use App\Entity\Measure;
use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Form\AddRoomType;
use App\Form\FilterAndSort;
use App\Form\SerchRoomASType;
use App\Repository\MeasureRepository;
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
use App\Form\FilterAndSortTechnician;
use App\Service\DiagnocticService;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

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

        $form = $this->createForm(FilterAndSort::class);
        $form->handleRequest($request);

        //Verify the current date
        $currentDate = new \DateTime();
        $season = $this->getSeason($currentDate);

        // filter tri choice
        $filterChoice = $form->get('filter')->getData();
        $sortChoice   = $form->get('trier')->getData();

        // room filter
        $rooms = match ($filterChoice) {
            'withSA' => $roomRepository->findAllWithIdSa(),
            'withoutSA' => $roomRepository->findAllWithoutIdSa(),
            default => $roomRepository->findAllOrderedByRoomName(),
        };

        $summerNorms = $normRepository->findOneBy([
            'NormType'   => 'confort',
            'NormSeason' => 'été'
        ]);

        $winterNorms = $normRepository->findOneBy([
            'NormType'   => 'confort',
            'NormSeason' => 'hiver'
        ]);

        // add diagnostic status to room
        foreach ($rooms as $room) {
            $sa = $room->getSa();
            $diagnosticColor = $diagnosticService->getDiagnosticStatus($sa, $room, $summerNorms, $winterNorms);
            $room->setDiagnosticStatus($diagnosticColor);
        }

        // sort
        if (in_array($sortChoice, ['Dia'])) {
            $choice = match ($sortChoice) {
                'Dia' => 1,
                default => 0,
            };

            $rooms = $roomRepository->sortRoomsByState($rooms, $choice);
        }

        // Prepare data for the template
        $roomsWithDiagnostics = array_map(function(Room $room) {
            return [
                'room'             => $room,
                'diagnosticStatus' => $room->getDiagnosticStatus(),
            ];
        }, $rooms);

        return $this->render('room/index.html.twig', [
            'form'  => $form->createView(),
            'rooms' => $roomsWithDiagnostics,
            'season' => $season,
        ]);
    }

    /**
    * Description: Verify if the current date is in the summer period. Return 'été' if it is, 'hiver' if not.
    */
    public function getSeason(\DateTime $date): string
    {
        $startSummer = new \DateTime('March 20');
        $endSummer = new \DateTime('September 22');

        if ($date >= $startSummer && $date <= $endSummer) {
            return 'été';
        }

        return 'hiver';
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
        MeasureRepository $measureRepository, // Ajout de MeasureRepository pour roomHistory
        ChartBuilderInterface $chartBuilder // Ajout de ChartBuilderInterface pour roomHistory
    ): Response {
        // Trouver la salle par son nom
        $room = $roomRepository->findByRoomName($roomName);
        $down = null;

        // Si la salle est introuvable, retourner un message d'erreur
        if (!$room) {
            return $this->render('room/not_found.html.twig', [
                'message' => 'Salle introuvable.',
            ]);
        }

        $currentDate = new \DateTime();
        $season = $this->getSeason($currentDate);

        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => $season
        ]);

        // Trouver un SA si il existe
        $sa = null;
        if ($room->getIdSa()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
            if ($sa && $sa->getState() == SAState::Down) {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        }

        // Gestion de l'historique de la salle avec les mesures et graphiques
        $temperatureMeasures = $measureRepository->findByTypeAndSa('temp', $sa ? $sa->getId() : null);
        $humidityMeasures = $measureRepository->findByTypeAndSa('hum', $sa ? $sa->getId() : null);
        $co2Measures = $measureRepository->findByTypeAndSa('co2', $sa ? $sa->getId() : null);

        $chartTemp = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chartTemp->setData([
            'datasets' => [
                [
                    'label' => 'Température',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'data' => $temperatureMeasures,
                ],
            ],
        ]);

        $chartHum = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartHum->setData([
            'datasets' => [
                [
                    'label' => 'Humidité',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'data' => $humidityMeasures,
                ],
            ],
        ]);

        $chartCO2 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartCO2->setData([
            'datasets' => [
                [
                    'label' => 'CO2',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'data' => $co2Measures,
                ],
            ],
        ]);

        // Rendre la vue avec l'ajout des graphiques et l'historique
        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'origin' => 'charge',
            'norms' => $norms,
            'down' => $down,
            'chartTemp' => $chartTemp,
            'chartHum' => $chartHum,
            'chartCO2' => $chartCO2,
        ]);
    }

    /**
     * Route: /technicien/salles/{roomName}
     * Name: app_room_info_technicien
     * Description: Displays detailed information about a specific room.
     */
    #[Route('/technicien/salles/{roomName}', name: 'app_room_info_technicien')]
    public function roomInfoTech(
        string $roomName,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        DownRepository $downRepo
    ): Response {
        // get room name
        $room = $roomRepository->findByRoomName($roomName);


        $sa = null;
        $down = null;
        $norms = null;


        $currentDate = new \DateTime();
        $season = $this->getSeason($currentDate);

        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' =>  $season
        ]);

        // 4. check if a sa is associate with room
        if ($room && $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());

            // if sa down get details
            if ($sa && $sa->getState() === SAState::Down) {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        }


        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'down' => $down,
            'norms' => $norms,
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
