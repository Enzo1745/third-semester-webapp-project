<?php

namespace App\Controller;

use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\ComfortInstruction;
use App\Entity\ComfortInstructionRoom;
use App\Form\SearchRoomsType;
use App\Repository\DownRepository;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use App\Repository\TipsRepository;
use App\Repository\ComfortInstructionRoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /**
     * @brief default page
     */
    #[Route('/', name: 'app_home')]
    public function roomInfo(
        Request $request,
        RoomRepository $roomRepository,
        DownRepository $downRepo,
        EntityManagerInterface $entityManager,
        TipsRepository $tipsRepo,
        ComfortInstructionRoomRepository $comfortInstructionRoomRepo
    ): Response
    {
        $tips = $tipsRepo->findRandTips(); // return a random tips from database

        $form = $this->createForm(SearchRoomsType::class);
        $form->handleRequest($request);

        // Get selected room name
        $roomName = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRoom = $form->get('salle')->getData();
            if ($selectedRoom) {
                $roomName = $selectedRoom->getRoomName();
            }
        }

        // Get the room by room name
        $room = null;
        $instructions = [];
        if (!empty($roomName)) {
            $room = $roomRepository->findByRoomNameWithSA($roomName);
            if ($room) {
                $this->giveInstructionsForRoom($room, $entityManager);
                $instructions = $comfortInstructionRoomRepo->findBy(['room' => $room]);
            }
        }

        // Determine the current season and retrieve the appropriate norms
        $currentDate = new \DateTime();
        $season = $this->getSeason($currentDate);

        // Get the norms
        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => $season
        ]);

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
            'form' => $form->createView(),
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
     * Gives tasks to the user depending on the conditions of a given room
     */
    private function giveInstructionsForRoom(Room $room, EntityManagerInterface $entityManager): void
    {
        $sa = $room->getIdSA() ? $entityManager->getRepository(Sa::class)->find($room->getIdSA()) : null;
        $norms = $entityManager->getRepository(Norm::class)->findOneBy([
            'NormType' => "Confort"
        ]);

        // Apply comfort instructions based on conditions
        if ($sa->getCo2() > $norms->getCo2MaxNorm() || $sa->getHumidity() > $norms->getHumidityMaxNorm()) {
            $this->applyComfortInstruction($room->getId(), 1, $entityManager); // Open window
            $this->applyComfortInstruction($room->getId(), 2, $entityManager); // Open door
        }

        if ($sa->getTemperature() < $norms->getTemperatureMinNorm()) {
            $this->applyComfortInstruction($room->getId(), 3, $entityManager); // Turn on heater
        } elseif ($sa->getTemperature() > $norms->getTemperatureMaxNorm()) {
            $this->applyComfortInstruction($room->getId(), 4, $entityManager); // Turn off heater
        }
    }

    /**
     * Determines the current season (summer or winter).
     */
    private function getSeason(\DateTime $date): string
    {
        $startSummer = new \DateTime('7 April');
        $startWinter = new \DateTime('6 October');

        return ($date >= $startSummer && $date < $startWinter) ? "Été" : "Hiver";
    }

    /**
     * The $instructionId parameter determines which comfort instruction is applied:
     * - 1: Open the window
     * - 2: Open the door
     * - 3: Turn on the heater
     * - 4: Turn off the heater
     */
    public function applyComfortInstruction(int $roomId, int $instructionId, EntityManagerInterface $entityManager): void
    {
        // Get room and comfort instruction by their IDs
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        $instruction = $entityManager->getRepository(ComfortInstruction::class)->find($instructionId);

        // Create and associate the comfort instruction with the room
        $comfortInstructionRoom = new ComfortInstructionRoom();
        $comfortInstructionRoom->setRoom($room);
        $comfortInstructionRoom->setComfortInstruction($instruction);

        $entityManager->persist($comfortInstructionRoom);
        $entityManager->flush();
    }
}
