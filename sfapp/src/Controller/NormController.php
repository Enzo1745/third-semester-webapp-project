<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NormController extends AbstractController
{
    #[Route('/charge/salles/normes', name: 'app_norm_show')]
    public function showNorm(): Response
    {
        return $this->render('norm/index.html.twig', [
        ]);
    }
}
