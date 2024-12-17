<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Sa;
use App\Form\SearchSaASType;
use App\Form\SerchRoomASType;
use App\Repository\Model\NormSeason;
use App\Repository\NormRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\SaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Model\SAState;

/**
 * @brief Controller of the list of sa
 */
class SaListController extends AbstractController
{

    /**
     * @brief Function to return the list of sa in the web page
     * @param Request $request
     * @param SaRepository $saRepo
     * @return Response -> return the web page with the list of all sa
     */
    #[Route('/charge/gestion_sa', name: 'app_gestion_sa')]
    public function listSA(Request $request, SaRepository $saRepo): Response
    {
        $saList = $saRepo->findAll(); // Function used to return all the sa in the database.

        return $this->render('gestion_sa/list.html.twig', [
            "saList" => $saList
        ]);
    }

    /**
     * @brief Function to show a list of SA in the web page
     * @param Request $request
     * @param SaRepository $saRepo
     * @param EntityManagerInterface $entityManager
     * @param NormRepository $normRepository
     * @return Response Returns the web page with the SA list and diagnostics
     */
    /**
     * @brief Function to show a list of SA in the web page
     * @param Request $request
     * @param SaRepository $saRepo
     * @param EntityManagerInterface $entityManager
     * @param NormRepository $normRepository
     * @return Response Returns the web page with the SA list and diagnostics
     */
    /**
     * @brief Function to show a list of SA in the web page
     * @param Request $request
     * @param SaRepository $saRepo
     * @param EntityManagerInterface $entityManager
     * @param NormRepository $normRepository
     * @return Response Returns the web page with the SA list and diagnostics
     */
    #[Route('/technicien/sa', name: 'app_technician_sa')]
    public function techListSa(
        Request $request,
        SaRepository $saRepo,
        EntityManagerInterface $entityManager,
        NormRepository $normRepository
    ): Response {
        // Create the search form and handle the request
        $form = $this->createForm(SearchSaASType::class);
        $form->handleRequest($request);

        // Retrieve filter choice from the form
        $choice = $form->get('filter')->getData();

        // Filter the SA list based on the selected state
        if ($choice === 'SADown') {
            $saList = $saRepo->findBy(['state' => SAState::Down]);
        } elseif ($choice === 'SAWaiting') {
            $saList = $saRepo->findBy(['state' => SAState::Waiting]);
        } elseif ($choice === 'SAInstall') {
            $saList = $saRepo->findBy(['state' => SAState::Installed]);
        } elseif ($choice === 'SAAvailable') {
            $saList = $saRepo->findBy(['state' => SAState::Available]);
        } else {
            $saList = $saRepo->findAll(); // Default: no filter applied
        }

        // Retrieve the technical norms for summer
        $norm = $normRepository->findOneBy([
            'NormType' => 'technique',
            'NormSeason' => 'été'
        ]);

        // Extract norms values for diagnostics
        $temperatureMin = $norm->getTemperatureMinNorm();
        $temperatureMax = $norm->getTemperatureMaxNorm();
        $humidityMin = $norm->getHumidityMinNorm();
        $humidityMax = $norm->getHumidityMaxNorm();
        $co2Min = $norm->getCo2MinNorm();
        $co2Max = $norm->getCo2MaxNorm();

        // Assign diagnostic status to each SA
        foreach ($saList as $sa) {
            $diagnosticStatus = 'grey'; // Default color

            if ($sa->getRoom()) {
                $temperature = $sa->getTemperature();
                $humidity = $sa->getHumidity();
                $co2 = $sa->getCo2();

                // Logic for diagnostic color
                // Logic for diagnostic color
                if ($temperature === null || $humidity === null || $co2 === null) {
                    $diagnosticStatus = 'grey';
                } elseif (
                    $temperature < $temperatureMin || $temperature > $temperatureMax &&
                    $humidity < $humidityMin || $humidity > $humidityMax &&
                    $co2 < $co2Min || $co2 > $co2Max
                ) {
                    $diagnosticStatus = 'red';
                } elseif (
                    $temperature >= $temperatureMin && $temperature <= $temperatureMax &&
                    $humidity >= $humidityMin && $humidity <= $humidityMax &&
                    $co2 >= $co2Min && $co2 <= $co2Max
                ) {
                    $diagnosticStatus = 'green';
                } else {
                    $diagnosticStatus = 'yellow';
                }


            }

            // Attach diagnostic status to the SA
            $sa->diagnosticStatus = $diagnosticStatus;
        }

        // Render the view with the form and SA list
        return $this->render('gestion_sa/technicianList.html.twig', [
            'form' => $form->createView(),
            'saList' => $saList,
        ]);
    }




    /**
     * @brief Function to delete a system acquisition
     * @param int $id The ID of the system acquisition to delete
     * @param SaRepository $saRepo The repository to access the system acquisition data
     * @param EntityManagerInterface $entityManager The EntityManager to manage persistence operations
     * @return Response The response that redirects to the list of system acquisitions
     */
    #[Route('/technicien/sa/delete/{id}', name: 'app_sa_delete', methods: ['POST'])]
    public function deleteSa(int $id, SaRepository $saRepo, EntityManagerInterface $entityManager): Response
    {
        $sa = $saRepo->find($id);

        if (!$sa) {
            $this->addFlash('error', 'Système d\'acquisition non trouvé');
            return $this->redirectToRoute('app_technician_sa');
        }

        if ($sa->getRoom()) {
            $room = $sa->getRoom();
            $sa->setRoom(null);
            $room->setSa(null);
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
        }

        $entityManager->remove($sa);
        $entityManager->flush();

        $this->addFlash('success', 'Système d\'acquisition supprimé avec succès');

        return $this->redirectToRoute('app_technician_sa');


    }

    public function getDiagnosticStatus(Room $room, EntityManagerInterface $entityManager, NormRepository $normRepository): string
    {
        $this->normRepository = $normRepository;

        // Fetch the norms for summer season
        $summerNorms = $this->normRepository->findOneBy(['NormSeason' => NormSeason::Summer]);

        $sa = null;
        if ($room->getIdSA()) {
            $sa = $entityManager->getRepository(Sa::class)->find($room->getIdSA());
        }
        if (!$sa or $sa->getCO2() == null or $sa->getTemperature() == null or $sa->getHumidity() == null or $sa->getState() == SAState::Waiting) {
            return 'grey';  // No functional SA found for the room, so no diagnostic
        }




        $temperatureCompliant = $sa->getTemperature() >= $summerNorms->getTemperatureMinNorm() &&
            $sa->getTemperature() <= $summerNorms->getTemperatureMaxNorm();
        $humidityCompliant = $sa->getHumidity() >= $summerNorms->getHumidityMinNorm() &&
            $sa->getHumidity() <= $summerNorms->getHumidityMaxNorm();
        $co2Compliant = $sa->getCO2() >= $summerNorms->getCo2MinNorm() &&
            $sa->getCO2() <= $summerNorms->getCo2MaxNorm();

        // Determine the diagnostic status
        $compliantCount = 0;
        if ($temperatureCompliant) {
            $compliantCount++;
        }
        if ($humidityCompliant) {
            $compliantCount++;
        }
        if ($co2Compliant) {
            $compliantCount++;
        }

        // Logic for diagnostic color
        if ($compliantCount === 3) {
            return 'green';
        } elseif ($compliantCount === 0) {
            return 'red';
        } else {
            return 'yellow';
        }
    }
}
