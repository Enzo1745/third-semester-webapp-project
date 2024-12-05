<?php

namespace App\Controller;

use App\Entity\Norm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NormController extends AbstractController
{

    #[Route('/charge/salles/normes', name: 'app_norm_show')]
    public function showNorm(Request $request, EntityManagerInterface $entityManager): Response
    {

        $summerNorm = $entityManager->getRepository(Norm::class)->findOneBy(['season' => 'summer']);
        $winterNorm = $entityManager->getRepository(Norm::class)->findOneBy(['season' => 'winter']);

        return $this->render('norm/index.html.twig', [
            'summerNorm' => $summerNorm,
            'winterNorm' => $winterNorm
        ]);
    }
}
