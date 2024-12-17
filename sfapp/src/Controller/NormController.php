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

        $this->updateNorms($request, $summerNorm, $winterNorm, $normRepository, $entityManager);


        $activeSeason = $request->query->get('season', 'summer');

        return $this->render('norm/index.html.twig', [
            // Return norm value
            'summerNorm' => $summerNorm,
            'winterNorm' => $winterNorm,
            'activeSeason' => $activeSeason,
        ]);
    }


    private function updateNorms(Request $request, Norm $summerNorm, Norm $winterNorm, NormRepository $normRepository, EntityManagerInterface $entityManager): void
    {
        if ($request->isMethod('POST')) {
            // Récupérer les normes techniques
            $technicalSummer = $normRepository->findTechnicalLimitsBySeason(NormSeason::Summer);
            $technicalWinter = $normRepository->findTechnicalLimitsBySeason(NormSeason::Winter);

            $errors = [];

            // Vérification des normes d'été
            if ($request->request->get('humidityMinNormSummer')) {
                $humidityMinSummer = $request->request->get('humidityMinNormSummer');
                $humidityMaxSummer = $request->request->get('humidityMaxNormSummer');
                $temperatureMinSummer = $request->request->get('temperatureMinNormSummer');
                $temperatureMaxSummer = $request->request->get('temperatureMaxNormSummer');
                $co2MinSummer = $request->request->get('co2MinNormSummer');
                $co2MaxSummer = $request->request->get('co2MaxNormSummer');

                // Comparaison avec les normes techniques
                if ($humidityMinSummer < $technicalSummer->getHumidityMinNorm() || $humidityMaxSummer > $technicalSummer->getHumidityMaxNorm()) {
                    $errors[] = "Humidité pour l'été doit être entre {$technicalSummer->getHumidityMinNorm()}% et {$technicalSummer->getHumidityMaxNorm()}%.";
                }
                if ($temperatureMinSummer < $technicalSummer->getTemperatureMinNorm() || $temperatureMaxSummer > $technicalSummer->getTemperatureMaxNorm()) {
                    $errors[] = "Température pour l'été doit être entre {$technicalSummer->getTemperatureMinNorm()}°C et {$technicalSummer->getTemperatureMaxNorm()}°C.";
                }
                if ($co2MinSummer < $technicalSummer->getCo2MinNorm() || $co2MaxSummer > $technicalSummer->getCo2MaxNorm()) {
                    $errors[] = "CO2 pour l'été doit être entre {$technicalSummer->getCo2MinNorm()} ppm et {$technicalSummer->getCo2MaxNorm()} ppm.";
                }

                if (empty($errors)) {
                    $summerNorm->setHumidityMinNorm($humidityMinSummer)
                        ->setHumidityMaxNorm($humidityMaxSummer)
                        ->setTemperatureMinNorm($temperatureMinSummer)
                        ->setTemperatureMaxNorm($temperatureMaxSummer)
                        ->setCo2MinNorm($co2MinSummer)
                        ->setCo2MaxNorm($co2MaxSummer);
                }
            }

            // Vérification des normes d'hiver
            if ($request->request->get('humidityMinNormWinter')) {
                $humidityMinWinter = $request->request->get('humidityMinNormWinter');
                $humidityMaxWinter = $request->request->get('humidityMaxNormWinter');
                $temperatureMinWinter = $request->request->get('temperatureMinNormWinter');
                $temperatureMaxWinter = $request->request->get('temperatureMaxNormWinter');
                $co2MinWinter = $request->request->get('co2MinNormWinter');
                $co2MaxWinter = $request->request->get('co2MaxNormWinter');

                if ($humidityMinWinter < $technicalWinter->getHumidityMinNorm() || $humidityMaxWinter > $technicalWinter->getHumidityMaxNorm()) {
                    $errors[] = "Humidité pour l'hiver doit être entre {$technicalWinter->getHumidityMinNorm()}% et {$technicalWinter->getHumidityMaxNorm()}%.";
                }
                if ($temperatureMinWinter < $technicalWinter->getTemperatureMinNorm() || $temperatureMaxWinter > $technicalWinter->getTemperatureMaxNorm()) {
                    $errors[] = "Température pour l'hiver doit être entre {$technicalWinter->getTemperatureMinNorm()}°C et {$technicalWinter->getTemperatureMaxNorm()}°C.";
                }
                if ($co2MinWinter < $technicalWinter->getCo2MinNorm() || $co2MaxWinter > $technicalWinter->getCo2MaxNorm()) {
                    $errors[] = "CO2 pour l'hiver doit être entre {$technicalWinter->getCo2MinNorm()} ppm et {$technicalWinter->getCo2MaxNorm()} ppm.";
                }

                if (empty($errors)) {
                    $winterNorm->setHumidityMinNorm($humidityMinWinter)
                        ->setHumidityMaxNorm($humidityMaxWinter)
                        ->setTemperatureMinNorm($temperatureMinWinter)
                        ->setTemperatureMaxNorm($temperatureMaxWinter)
                        ->setCo2MinNorm($co2MinWinter)
                        ->setCo2MaxNorm($co2MaxWinter);
                }
            }

            // Gestion des erreurs
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
                return;
            }

            // Mise à jour des données si aucune erreur
            $entityManager->flush();
            $this->addFlash('success', 'Les normes ont été mises à jour avec succès.');
        }
    }

}