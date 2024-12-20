<?php

namespace App\Controller;

use App\Repository\TipsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /**
     * @brief default page
     */
    #[Route('/', name: 'app_home')]
    public function index(TipsRepository $tipsRepo ): Response
    {
        $tips = $tipsRepo->findRandTips(); // return a random tips from database

        return $this->render('home/index.html.twig', [
            'tips' => $tips,
        ]);
    }
}
