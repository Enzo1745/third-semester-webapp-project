<?php

namespace App\Controller;

use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\ComfortInstruction;
use App\Entity\ComfortInstructionRoom;
use App\Form\SearchRoomsType;
use App\Form\InstructionsType;
use App\Repository\DownRepository;
use App\Repository\LastUpdateRepository;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use App\Repository\TipsRepository;
use App\Repository\ComfortInstructionRoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\DiagnocticService;

/**
 * @brief The controller for the default page
 */
class HomeController extends AbstractController
{
    /**
     * @param Request $request
     * @param RoomRepository $roomRepository
     * @param DownRepository $downRepo
     * @param EntityManagerInterface $entityManager
     * @param TipsRepository $tipsRepo
     * @return Response
     * @brief Function to render the default page, sending the tips and the data of the selected room to it
     */
    #[Route('/', name: 'app_home')]
    public function roomInfo(
        Request $request,
        RoomRepository $roomRepository,
        DownRepository $downRepo,
        EntityManagerInterface $entityManager,
        TipsRepository $tipsRepo,
        ComfortInstructionRoomRepository $comfortInstructionRoomRepo,
        DiagnocticService $diagnosticService,
        LastUpdateRepository $lastUpdateRepo,
        ApiController $apiController,
        RoomController $roomController,
    ): Response
    {
        $apiResponse = $apiController->getDataFromApiIn2025($lastUpdateRepo);

        if ($apiResponse->getStatusCode() !== 200) {
            return new Response('Erreur lors de la récupération des données : ' . $apiResponse->getContent(), 500);
        }

        $tips = $tipsRepo->findRandTips(); // return a random tips from database

        $searchForm = $this->createForm(SearchRoomsType::class);
        $searchForm->handleRequest($request);

        $controller = $roomController;
        $form = $this->createForm(SearchRoomsType::class);

        $form->handleRequest($request);

        $roomName = '';

        // Get selected room name
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRoom = $form->get('salle')->getData();
            if ($selectedRoom) {
                $roomName = $selectedRoom->getRoomName();
            }
        }

        // Get the room from the room name
        $room = null;
        if (!empty($roomName)) {
            $room = $roomRepository->findByRoomNameWithSA($roomName);
        }
        $down = null;

        $currentDate = new \DateTime();
        $season = $diagnosticService->getSeason($currentDate);

        // Get the norms
        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => $season
        ]);

        // Get the room by room name and its instructions
        $room = null;
        $instructions = [];
        if (!empty($roomName)) {
            $room = $roomRepository->findByRoomNameWithSA($roomName);
            if ($room) {
                $this->deleteInstructionsForRoom($room, $entityManager); // Delete the instructions so that each time we get the updated instructions
                $this->giveInstructionsForRoom($room, $norms, $entityManager);
                $instructions = $comfortInstructionRoomRepo->findBy(['room' => $room]);
            }
        }

//        $instructionsForm = $this->createForm(InstructionsType::class, null, [
//            'instructions' => $instructions,
//        ]);
//        $instructionsForm->handleRequest($request);

//        if ($instructionsForm->isSubmitted() && $instructionsForm->isValid()) {
//            foreach ($instructionsForm->get('instructions')->getData() as $instructionId => $isConfirmed) {
//                if ($isConfirmed) {
//                    $instruction = $comfortInstructionRoomRepo->find($instructionId);
//                    if (!$instruction->getDoneByUserDate()) {
//                        $instruction->setDoneByUserDate(new \DateTime());
//                        $entityManager->persist($instruction);
//                    }
//                }
//            }
//            $entityManager->flush();
//            return $this->redirectToRoute('app_home');
//        }

        // Get the room SA
        $sa = null;
        $down = null;
        if ($room and $room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
            if ($sa && $sa->getState() == SAState::Down) {
                $down = $downRepo->findOneBy(['sa' => $sa]);
            }
        }

        return $this->render('home/index.html.twig', [
            'searchForm' => $searchForm->createView(),
//            'instructionsForm' => $instructionsForm->createView(),
            'tips' => $tips,
            'room' => $room,
            'sa' => $sa,
            'origin' => 'charge',
            'norms' => $norms,
            'down' => $down,
            'instructions' => $instructions,
        ]);
    }

    /**
     * Deletes tasks on a room
     */
    public function deleteInstructionsForRoom(Room $room, EntityManagerInterface $entityManager): void
    {
        $comfortInstructionRoomRepo = $entityManager->getRepository(ComfortInstructionRoom::class);
        $instructions = $comfortInstructionRoomRepo->findBy(['room' => $room]);
        foreach ($instructions as $instruction) {
            $entityManager->remove($instruction);
        }
        $entityManager->flush();
    }

    /**
     * Gives tasks to the user depending on the conditions of a given room
     */
    public function giveInstructionsForRoom(Room $room, Norm $norms, EntityManagerInterface $entityManager): void
    {
        $sa = $room->getIdSA() ? $entityManager->getRepository(Sa::class)->find($room->getIdSA()) : null;

        // Apply comfort instructions based on conditions
        if ($sa->getCo2() > $norms->getCo2MaxNorm() || $sa->getHumidity() > $norms->getHumidityMaxNorm()) {
            $this->applyComfortInstruction($room, 1, $entityManager); // Open window
            $this->applyComfortInstruction($room, 2, $entityManager); // Open door
        }

        if ($sa->getTemperature() < $norms->getTemperatureMinNorm()) {
            $this->applyComfortInstruction($room, 3, $entityManager); // Turn on heater
        } elseif ($sa->getTemperature() > $norms->getTemperatureMaxNorm()) {
            $this->applyComfortInstruction($room, 4, $entityManager); // Turn off heater
        }
    }

    /**
     * The $instructionId parameter determines which comfort instruction is applied:
     * - 1: Open the window
     * - 2: Open the door
     * - 3: Turn on the heater
     * - 4: Turn off the heater
     */
    public function applyComfortInstruction(Room $room, int $instructionId, EntityManagerInterface $entityManager): void
    {
        $instruction = $entityManager->getRepository(ComfortInstruction::class)->find($instructionId);

        // Check if the instruction is already linked to the room
        $existingInstruction = $entityManager->getRepository(ComfortInstructionRoom::class)->findOneBy([
            'room' => $room,
            'comfortInstruction' => $instruction
        ]);

        // If not already linked, create and persist the new instruction
        if (!$existingInstruction && $instruction) {
            $comfortInstructionRoom = new ComfortInstructionRoom();
            $comfortInstructionRoom->setRoom($room);
            $comfortInstructionRoom->setComfortInstruction($instruction);

            $entityManager->persist($comfortInstructionRoom);
            $entityManager->flush();
        }
    }
}
