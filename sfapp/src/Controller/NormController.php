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

/**
 * @brief Contoller used to manage the norms of the website
 */
class NormController extends AbstractController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param NormRepository $normRepository
     * @return Response
     * @brief Managing the render of the the pert of the websit used to show and change the norms in the charge side of the site
     */
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

        $comfortSummerNorm = $normRepository->findBySeason(NormSeason::Summer, NormType::Comfort);
        $comfortWinterNorm = $normRepository->findBySeason(NormSeason::Winter, NormType::Comfort);

        $this->updateNorms($request, $summerNorm, $winterNorm, $normRepository, $entityManager);


        $activeSeason = $request->query->get('season', 'summer');

        return $this->render('norm/index.html.twig', [
            // Return norm value
            'summerNorm' => $summerNorm,
            'winterNorm' => $winterNorm,
            'activeSeason' => $activeSeason,
            'origin' => 'technical',
            'comfortSummerNorm' => $comfortSummerNorm,
            'comfortWinterNorm' => $comfortWinterNorm,
        ]);
    }

    /**
     * @param Request $request
     * @param Norm $summerNorm
     * @param Norm $winterNorm
     * @param NormRepository $normRepository
     * @param EntityManagerInterface $entityManager
     * @return void
     * @brief Takes in the values of norms and changes the old norms values based on if they are valid after a check with the techical norms and send errors if they are not valid
     */
    private function updateNorms(Request $request, Norm $summerNorm, Norm $winterNorm, NormRepository $normRepository, EntityManagerInterface $entityManager): void
    {
        if ($request->isMethod('POST')) {
            $origin = $request->request->get('origin', 'comfort'); // Determine if the origin is "comfort" or another type.
            $errors = []; // Initialize an array to collect validation errors.

            // Retrieve technical limits for summer and winter norms if the origin is "comfort".
            $technicalSummer = $origin === 'comfort' ? $normRepository->findTechnicalLimitsBySeason(NormSeason::Summer, NormType::Technical) : null;
            $technicalWinter = $origin === 'comfort' ? $normRepository->findTechnicalLimitsBySeason(NormSeason::Winter, NormType::Technical) : null;

            // If technical limits are required but not defined, display an error message and stop processing.
            if ($origin === 'comfort' && (!$technicalSummer || !$technicalWinter)) {
                $this->addFlash('danger', 'Les normes techniques ne sont pas définies.');
                return;
            }

            // Define a function to validate minimum and maximum values with optional technical limits.
            $validateMinMax = function ($min, $max, $label, $minLimit = null, $maxLimit = null) use (&$errors) {
                // Check if the values respect the technical limits.
                if (!is_null($minLimit) && !is_null($maxLimit)) {
                    if ($min < $minLimit || $max > $maxLimit) {
                        $errors[] = "$label doit être entre $minLimit et $maxLimit."; // Add error if limits are violated.
                    }
                }

                // Ensure the minimum value is less than or equal to the maximum value.
                if ($min > $max) {
                    $errors[] = "$label : la valeur minimale doit être inférieure ou égale à la valeur maximale.";
                }
            };

            // Validate summer norms
            if ($request->request->has('humidityMinNormSummer') && $request->request->has('humidityMaxNormSummer')) {
                $validateMinMax(
                    $request->request->get('humidityMinNormSummer'),
                    $request->request->get('humidityMaxNormSummer'),
                    'Humidité pour l’été',
                    $technicalSummer?->getHumidityMinNorm(),
                    $technicalSummer?->getHumidityMaxNorm()
                );
            }

            if ($request->request->has('temperatureMinNormSummer') && $request->request->has('temperatureMaxNormSummer')) {
                $validateMinMax(
                    $request->request->get('temperatureMinNormSummer'),
                    $request->request->get('temperatureMaxNormSummer'),
                    'Température pour l’été',
                    $technicalSummer?->getTemperatureMinNorm(),
                    $technicalSummer?->getTemperatureMaxNorm()
                );
            }

            if ($request->request->has('co2MinNormSummer') && $request->request->has('co2MaxNormSummer')) {
                $validateMinMax(
                    $request->request->get('co2MinNormSummer'),
                    $request->request->get('co2MaxNormSummer'),
                    'CO2 pour l’été',
                    $technicalSummer?->getCo2MinNorm(),
                    $technicalSummer?->getCo2MaxNorm()
                );
            }

            // Validate winter norms
            if ($request->request->has('humidityMinNormWinter') && $request->request->has('humidityMaxNormWinter')) {
                $validateMinMax(
                    $request->request->get('humidityMinNormWinter'),
                    $request->request->get('humidityMaxNormWinter'),
                    'Humidité pour l’hiver',
                    $technicalWinter?->getHumidityMinNorm(),
                    $technicalWinter?->getHumidityMaxNorm()
                );
            }

            if ($request->request->has('temperatureMinNormWinter') && $request->request->has('temperatureMaxNormWinter')) {
                $validateMinMax(
                    $request->request->get('temperatureMinNormWinter'),
                    $request->request->get('temperatureMaxNormWinter'),
                    'Température pour l’hiver',
                    $technicalWinter?->getTemperatureMinNorm(),
                    $technicalWinter?->getTemperatureMaxNorm()
                );
            }

            if ($request->request->has('co2MinNormWinter') && $request->request->has('co2MaxNormWinter')) {
                $validateMinMax(
                    $request->request->get('co2MinNormWinter'),
                    $request->request->get('co2MaxNormWinter'),
                    'CO2 pour l’hiver',
                    $technicalWinter?->getCo2MinNorm(),
                    $technicalWinter?->getCo2MaxNorm()
                );
            }

            // If there are validation errors, display them and stop  processing.
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error);
                }
                return;
            }

            // Update summer norms if provided in the request.
            if ($request->request->has('humidityMinNormSummer')) {
                $summerNorm->setHumidityMinNorm($request->request->get('humidityMinNormSummer'))
                    ->setHumidityMaxNorm($request->request->get('humidityMaxNormSummer'))
                    ->setTemperatureMinNorm($request->request->get('temperatureMinNormSummer'))
                    ->setTemperatureMaxNorm($request->request->get('temperatureMaxNormSummer'))
                    ->setCo2MinNorm($request->request->get('co2MinNormSummer'))
                    ->setCo2MaxNorm($request->request->get('co2MaxNormSummer'));
            }

            // Update winter norms if provided in the request.
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