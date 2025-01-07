<?php

namespace App\Controller;

use App\Entity\Norm;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
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
        $summerNorm = $normRepository->findBySeason(NormSeason::Summer,NormType::Comfort);
        $winterNorm = $normRepository->findBySeason(NormSeason::Winter,NormType::Comfort);

        $this->updateNorms($request, $summerNorm, $winterNorm, $normRepository, $entityManager);


        $activeSeason = $request->query->get('season', 'summer');

        return $this->render('norm/index.html.twig', [
            // Return norm value
            'summerNorm' => $summerNorm,
            'winterNorm' => $winterNorm,
            'activeSeason' => $activeSeason,
            'origin' => 'comfort',
        ]);
    }
    #[Route('/technicien/sa/normes', name: 'app_norm_tech_show')]
    public function showTechNorm(Request $request, EntityManagerInterface $entityManager, NormRepository $normRepository): Response
    {
        // Norm season find in DB
        $summerNorm = $normRepository->findBySeason(NormSeason::Summer, NormType::Technical);
        $winterNorm = $normRepository->findBySeason(NormSeason::Winter, NormType::Technical);

        $this->updateNorms($request, $summerNorm, $winterNorm, $normRepository, $entityManager);


        $activeSeason = $request->query->get('season', 'summer');

        return $this->render('norm/index.html.twig', [
            // Return norm value
            'summerNorm' => $summerNorm,
            'winterNorm' => $winterNorm,
            'activeSeason' => $activeSeason,
            'origin' => 'technical',
        ]);
    }


    private function updateNorms(Request $request, Norm $summerNorm, Norm $winterNorm, NormRepository $normRepository, EntityManagerInterface $entityManager): void
    {
        if ($request->isMethod('POST')) {
            $origin = $request->request->get('origin', 'comfort');
            $errors = [];

            if ($origin === 'comfort') {
                $technicalSummer = $normRepository->findTechnicalLimitsBySeason(NormSeason::Summer, NormType::Technical);
                $technicalWinter = $normRepository->findTechnicalLimitsBySeason(NormSeason::Winter, NormType::Technical);

                if (!$technicalSummer || !$technicalWinter) {
                    $this->addFlash('danger', 'Les normes techniques ne sont pas définies.');
                    return;
                }

                if ($request->request->has('humidityMinNormSummer') && $request->request->has('humidityMaxNormSummer')) {
                    $humidityMinSummer = $request->request->get('humidityMinNormSummer');
                    $humidityMaxSummer = $request->request->get('humidityMaxNormSummer');
                    if ($humidityMinSummer < $technicalSummer->getHumidityMinNorm() || $humidityMaxSummer > $technicalSummer->getHumidityMaxNorm()) {
                        $errors[] = "Humidité pour l'été doit être entre {$technicalSummer->getHumidityMinNorm()}% et {$technicalSummer->getHumidityMaxNorm()}%.";
                    }
                    if ($humidityMinSummer > $humidityMaxSummer) {
                        $errors[] = "Humidité pour l'été : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                    }
                }

                if ($request->request->has('temperatureMinNormSummer') && $request->request->has('temperatureMaxNormSummer')) {
                    $temperatureMinSummer = $request->request->get('temperatureMinNormSummer');
                    $temperatureMaxSummer = $request->request->get('temperatureMaxNormSummer');
                    if ($temperatureMinSummer < $technicalSummer->getTemperatureMinNorm() || $temperatureMaxSummer > $technicalSummer->getTemperatureMaxNorm()) {
                        $errors[] = "Température pour l'été doit être entre {$technicalSummer->getTemperatureMinNorm()}°C et {$technicalSummer->getTemperatureMaxNorm()}°C.";
                    }
                    if ($temperatureMinSummer > $temperatureMaxSummer) {
                        $errors[] = "Température pour l'été : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                    }
                }

                if ($request->request->has('co2MinNormSummer') && $request->request->has('co2MaxNormSummer')) {
                    $co2MinSummer = $request->request->get('co2MinNormSummer');
                    $co2MaxSummer = $request->request->get('co2MaxNormSummer');
                    if ($co2MinSummer < $technicalSummer->getCo2MinNorm() || $co2MaxSummer > $technicalSummer->getCo2MaxNorm()) {
                        $errors[] = "CO2 pour l'été doit être entre {$technicalSummer->getCo2MinNorm()} ppm et {$technicalSummer->getCo2MaxNorm()} ppm.";
                    }
                    if ($co2MinSummer > $co2MaxSummer) {
                        $errors[] = "CO2 pour l'été : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                    }
                }

                if ($request->request->has('humidityMinNormWinter') && $request->request->has('humidityMaxNormWinter')) {
                    $humidityMinWinter = $request->request->get('humidityMinNormWinter');
                    $humidityMaxWinter = $request->request->get('humidityMaxNormWinter');
                    if ($humidityMinWinter < $technicalWinter->getHumidityMinNorm() || $humidityMaxWinter > $technicalWinter->getHumidityMaxNorm()) {
                        $errors[] = "Humidité pour l'hiver doit être entre {$technicalWinter->getHumidityMinNorm()}% et {$technicalWinter->getHumidityMaxNorm()}%.";
                    }
                    if ($humidityMinWinter > $humidityMaxWinter) {
                        $errors[] = "Humidité pour l'hiver : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                    }
                }

                if ($request->request->has('temperatureMinNormWinter') && $request->request->has('temperatureMaxNormWinter')) {
                    $temperatureMinWinter = $request->request->get('temperatureMinNormWinter');
                    $temperatureMaxWinter = $request->request->get('temperatureMaxNormWinter');
                    if ($temperatureMinWinter < $technicalWinter->getTemperatureMinNorm() || $temperatureMaxWinter > $technicalWinter->getTemperatureMaxNorm()) {
                        $errors[] = "Température pour l'hiver doit être entre {$technicalWinter->getTemperatureMinNorm()}°C et {$technicalWinter->getTemperatureMaxNorm()}°C.";
                    }
                    if ($temperatureMinWinter > $temperatureMaxWinter) {
                        $errors[] = "Température pour l'hiver : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                    }
                }

                if ($request->request->has('co2MinNormWinter') && $request->request->has('co2MaxNormWinter')) {
                    $co2MinWinter = $request->request->get('co2MinNormWinter');
                    $co2MaxWinter = $request->request->get('co2MaxNormWinter');
                    if ($co2MinWinter < $technicalWinter->getCo2MinNorm() || $co2MaxWinter > $technicalWinter->getCo2MaxNorm()) {
                        $errors[] = "CO2 pour l'hiver doit être entre {$technicalWinter->getCo2MinNorm()} ppm et {$technicalWinter->getCo2MaxNorm()} ppm.";
                    }
                    if ($co2MinWinter > $co2MaxWinter) {
                        $errors[] = "CO2 pour l'hiver : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                    }
                }
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
                return;
            }

            if ($request->request->has('humidityMinNormSummer')) {
                $summerNorm->setHumidityMinNorm($request->request->get('humidityMinNormSummer'))
                    ->setHumidityMaxNorm($request->request->get('humidityMaxNormSummer'))
                    ->setTemperatureMinNorm($request->request->get('temperatureMinNormSummer'))
                    ->setTemperatureMaxNorm($request->request->get('temperatureMaxNormSummer'))
                    ->setCo2MinNorm($request->request->get('co2MinNormSummer'))
                    ->setCo2MaxNorm($request->request->get('co2MaxNormSummer'));
            }

            if ($request->request->has('humidityMinNormWinter')) {
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