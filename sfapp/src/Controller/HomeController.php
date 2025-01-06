<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_room_info')]
    public function index(RoomRepository $roomRepository): Response
    {
//        $room = $roomRepository->findByRoomName($roomName);

        return $this->render('home/room.html.twig', [
//            'room' => $room
        ]);
    }
}
