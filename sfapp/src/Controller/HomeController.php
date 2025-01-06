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
        TipsRepository $tipsRepo
    ): Response
    {
        $tips = $tipsRepo->findRandTips(); // return a random tips from database

        $form = $this->createForm(SearchRoomsType::class);

        $form->handleRequest($request);

        $roomName = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRoom = $form->get('salle')->getData();
            if ($selectedRoom) {
                $roomName = $selectedRoom->getRoomName();
            }
        }

        $room = null;
        if (!empty($roomName)) {
            $room = $roomRepository->findByRoomNameWithSA($roomName);
        }
        $down = null;

        $normRepository = $entityManager->getRepository(Norm::class);
        $norms = $normRepository->findOneBy([
            'NormType' => 'confort',
            'NormSeason' => 'été'
        ]);

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
