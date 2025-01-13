<?php

namespace App\Controller;

use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Form\SearchRoomsType;
use App\Repository\DownRepository;
use App\Repository\Model\SAState;
use App\Repository\RoomRepository;
use App\Repository\TipsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        TipsRepository $tipsRepo
    ): Response
    {
        $tips = $tipsRepo->findRandTips(); // return a random tips from database

        $controller = new RoomController();
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
        $season = $controller->getSeason($currentDate);
        // Get the norms
        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => $season
        ]);

        // Get the room SA
        $sa = null;
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
        ]);
    }
}
