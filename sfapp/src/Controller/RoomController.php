<?php

namespace App\Controller;

use App\Entity\Measure;
use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Form\AddRoomType;
use App\Form\DateCaptureType;
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
     * @return Response
     * @brief renders the main charge section page (/charge/salles) when th users goes to the /charge page
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
     * Displays a form to add a room and processes form submissions.
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @brief Renders the page to add a room
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
     * @param RoomRepository $roomRepository
     * @param DiagnocticService $diagnosticService
     * @param NormRepository $normRepository
     * @param Request $request
     * @return Response
     * @brief Render the main page of the charge part, managing the filters in it and sending the data of the rooms
     */
    #[Route('/charge/salles', name: 'app_room_list')]
    public function listRooms(
        RoomRepository $roomRepository,
        DiagnocticService $diagnosticService,
        NormRepository $normRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_CHARGE');

        $form = $this->createForm(FilterAndSort::class);
        $form->handleRequest($request);

        //Verify the current date
        $currentDate = new \DateTime();
        $season = $diagnosticService->getSeason($currentDate);

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
     * Route: /charge/salles/{roomName}
     * Name: app_room_info
     * Description: Displays detailed information about a specific room.
     * @throws \DateMalformedStringException
     * @param string $roomName
     * @param RoomRepository $roomRepository
     * @param DownRepository $downRepo
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @brief Displays detailed information about a specific room.
     */
    #[Route('/charge/salles/{roomName}', name: 'app_room_info')]
    public function roomInfo(
        string $roomName,
        RoomRepository $roomRepository,
        DownRepository $downRepo,
        EntityManagerInterface $entityManager,
        MeasureRepository $measureRepository, // Add MeasureRepository for roomHistory
        ChartBuilderInterface $chartBuilder, // Add ChartBuilderInterface for roomHistory
        Request $request,
        DiagnocticService $diagnosticService,
    ): Response {
        // Find a room by its name
        $room = $roomRepository->findByRoomName($roomName);
        $down = null;

        // Set default date range for measures
        $dateDebut = new \DateTime("2025-01-01");
        $dateFin = new \DateTime("2025-12-31");

        $data = [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ];

        // Create the form with the custom type
        $form = $this->createForm(DateCaptureType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];
        }

        // If the room is not found, return an error message
        if (!$room) {
            return $this->render('room/not_found.html.twig', [
                'message' => 'Salle introuvable.',
            ]);
        }

        // Get the current date and determine the season
        $currentDate = new \DateTime();
        $season = $diagnosticService->getSeason($currentDate);

        // Fetch norms based on the season
        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => $season
        ]);

        // Find the SA if it exists
        $sa = null;
        if ($room->getIdSa()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
            if ($sa && $sa->getState() == SAState::Down) {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        }

        // Fetch measures for the room
        $temperatureMeasures = $measureRepository->findByTypeAndSa('temp', $sa ? (string) $sa->getId() : null);
        $humidityMeasures = $measureRepository->findByTypeAndSa('hum', $sa ? (string) $sa->getId() : null);
        $co2Measures = $measureRepository->findByTypeAndSa('co2', $sa ? (string) $sa->getId() : null);

        // Create temperature chart
        $chartTemp = $chartBuilder->createChart(Chart::TYPE_LINE);

        $TempLabelList = [];
        $TempValueList = [];

        $HumLabelList = [];
        $HumValueList = [];

        $Co2LabelList = [];
        $Co2ValueList = [];

        // Populate temperature data for the chart
        foreach ($temperatureMeasures as $measure) {
            $TempValueList[] = $measure['value'];
            if ($measure['captureDate'] >= $dateDebut && $measure['captureDate'] < $dateFin) {
                $TempLabelList[] = $measure['captureDate']->format('Y-m-d');
            }
        }

        // Populate humidity data for the chart
        foreach ($humidityMeasures as $measure) {
            $HumValueList[] = $measure['value'];
            if ($measure['captureDate'] >= $dateDebut && $measure['captureDate'] < $dateFin) {
                $HumLabelList[] = $measure['captureDate']->format('Y-m-d');
            }
        }

        // Populate CO2 data for the chart
        foreach ($co2Measures as $measure) {
            $Co2ValueList[] = $measure['value'];
            if ($measure['captureDate'] >= $dateDebut && $measure['captureDate'] < $dateFin) {
                $Co2LabelList[] = $measure['captureDate']->format('Y-m-d');
            }
        }

        // Set data and options for the temperature chart
        $chartTemp->setData([
            'labels' => $TempLabelList,
            'datasets' => [
                [
                    'label' => 'Température',
                    'backgroundColor' => 'rgba(255, 0, 0, 0.2)',
                    'borderColor' => 'rgba(255, 0, 0, 1)',
                    'data' => $TempValueList,
                ],
            ],
        ]);

        $chartTemp->setOptions([
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Température (Celsius)',
                    ],
                    'min' => 10,
                    'max' => 30,
                ],
            ],
        ]);

        // Create and set data and options for the humidity chart
        $chartHum = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartHum->setData([
            'labels' => $HumLabelList,
            'datasets' => [
                [
                    'label' => 'Humidité',
                    'backgroundColor' => 'rgba(0, 0, 255, 0.2)',
                    'borderColor' => 'rgba(0, 0, 255, 1)',
                    'data' => $HumValueList,
                ],
            ],
        ]);

        $chartHum->setOptions([
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Humidité (Pourcentage)',
                    ],
                    'min' => 0,
                    'max' => 100,
                ],
            ],
        ]);

        // Create and set data and options for the CO2 chart
        $chartCO2 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartCO2->setData([
            'labels' => $Co2LabelList,
            'datasets' => [
                [
                    'label' => 'CO2',
                    'backgroundColor' => 'rgba(0, 0, 0, 0.2)',
                    'borderColor' => 'rgba(0, 0, 0, 1)',
                    'data' => $Co2ValueList,
                ],
            ],
        ]);

        $chartCO2->setOptions([
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Concentration de CO2 (Parti par miliers)',
                    ],
                    'min' => 250,
                    'max' => 1000,
                ],
            ],
        ]);

        // Render the view with the added charts and history
        return $this->render('room/room_info.html.twig', [
            'room' => $room,
            'sa' => $sa,
            'origin' => 'charge',
            'norms' => $norms,
            'down' => $down,
            'chartTemp' => $chartTemp,
            'chartHum' => $chartHum,
            'chartCO2' => $chartCO2,
            'dateForm' => $form->createView(),
        ]);
    }

    /**
     * @param string $roomName
     * @param RoomRepository $roomRepository
     * @param EntityManagerInterface $entityManager
     * @param DownRepository $downRepo
     * @return Response
     * @brief Displays detailed information about a specific room.
     */
    #[Route('/technicien/salles/{roomName}', name: 'app_room_info_technicien')]
    public function roomInfoTech(
        string $roomName,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        DownRepository $downRepo,
        DiagnocticService $diagnosticService,
    ): Response {
        // get room name
        $room = $roomRepository->findByRoomName($roomName);


        $sa = null;
        $down = null;
        $norms = null;


        $currentDate = new \DateTime();
        $season = $diagnosticService->getSeason($currentDate);

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
            'dateForm' => null,
        ]);
    }


    /**
     * @param Room|null $room
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @brief Deletes a room.
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
