<?php

namespace App\Controller;

use App\Entity\Norm;
use App\Repository\Model\NormSeason;
use App\Repository\NormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NormController extends AbstractController
{

    #[Route('/charge/salles/normes', name: 'app_norm_show')]
    public function showNorm(Request $request, EntityManagerInterface $entityManager, NormRepository $normRepository): Response
    {
        // Norm season find in DB
        $summerNorm = $normRepository->findBySeason(NormSeason::Summer);
        $winterNorm = $normRepository->findBySeason(NormSeason::Winter);

        $this->updateNorms($request, $summerNorm, $winterNorm, $entityManager);

        $activeSeason = $request->query->get('season', 'summer');

        return $this->render('norm/index.html.twig', [
            // Return norm value
            'summerNorm' => $summerNorm,
            'winterNorm' => $winterNorm,
            'activeSeason' => $activeSeason,
        ]);
    }


    private function updateNorms(Request $request, Norm $summerNorm, Norm $winterNorm, EntityManagerInterface $entityManager): void
    {
        if ($request->isMethod('POST')) {
            if ($request->request->get('humidityMinNormSummer')) {
                $summerNorm->setHumidityMinNorm($request->request->get('humidityMinNormSummer'))
                    ->setHumidityMaxNorm($request->request->get('humidityMaxNormSummer'))
                    ->setTemperatureMinNorm($request->request->get('temperatureMinNormSummer'))
                    ->setTemperatureMaxNorm($request->request->get('temperatureMaxNormSummer'))
                    ->setCo2MinNorm($request->request->get('co2MinNormSummer'))
                    ->setCo2MaxNorm($request->request->get('co2MaxNormSummer'));
            }

            if ($request->request->get('humidityMinNormWinter')) {
                $winterNorm->setHumidityMinNorm($request->request->get('humidityMinNormWinter'))
                    ->setHumidityMaxNorm($request->request->get('humidityMaxNormWinter'))
                    ->setTemperatureMinNorm($request->request->get('temperatureMinNormWinter'))
                    ->setTemperatureMaxNorm($request->request->get('temperatureMaxNormWinter'))
                    ->setCo2MinNorm($request->request->get('co2MinNormWinter'))
                    ->setCo2MaxNorm($request->request->get('co2MaxNormWinter'));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les normes ont été mises à jour avec succès.');
        }
    }
}