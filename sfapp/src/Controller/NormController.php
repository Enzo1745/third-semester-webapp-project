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


        $season = $request->query->get('season', 'summer');

        $norm = $entityManager->getRepository(Norm::class)->findOneBy(['season' => $season]);

        if (!$norm) {
            throw $this->createNotFoundException("Aucune donnée trouvé dans la base de donnée");
        }

        return $this->render('norm/index.html.twig', [

            'humidityMinNorm' => $norm->getHumidityMinNorm(),
            'humidityMaxNorm' => $norm->getHumidityMaxNorm(),
            'temperatureMinNorm' => $norm->getTemperatureMinNorm(),
            'temperatureMaxNorm' => $norm->getTemperatureMaxNorm(),
            'co2MinNorm' => $norm->getCo2MinNorm(),
            'co2MaxNorm' => $norm->getCo2MaxNorm(),
        ]);
    }
}
