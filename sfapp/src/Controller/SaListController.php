<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Sa;
use App\Form\FilterTrier;
use App\Form\SerchRoomASType;
use App\Form\TrierFormType;
use App\Repository\Model\NormSeason;
use App\Repository\NormRepository;
use App\Service\DiagnocticService;
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
    #[Route('/charge/gestion_sa', name: 'app_sa_management')]
    public function listSA(Request $request, SaRepository $saRepo): Response
    {
        $saList = $saRepo->findAll(); // Function used to return all the sa in the database.

        return $this->render('sa_management/list.html.twig', [
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
    #[Route('/technicien/sa', name: 'app_technician_sa')]
    public function techListSa(
        Request                $request,
        SaRepository           $saRepo,
        EntityManagerInterface $entityManager,
        DiagnocticService      $diagnosticService,
        NormRepository         $normRepository

    ): Response {


        // Create the search form and handle the request
        $form = $this->createForm(FilterTrier::class);
        $form->handleRequest($request);


        // Retrieve filter choice from the form
        $choice = $form->get('filter')->getData();
        $trierChoice  = $form->get('trier')->getData();


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

                $summerNorms = $normRepository->findOneBy([
                      'NormType'   => 'technique',
                      'NormSeason' => 'été'
                  ]);

                  foreach ($saList as $sa) {
                      $diagnosticColor = $diagnosticService->getDiagnosticStatus($sa, $summerNorms);
                      $sa->setDiagnosticStatus($diagnosticColor);
                  }

                                                                                                     //tri by association (down,waiting,installed,available)
        if ($trierChoice === 'Asso') {
            $saList = $saRepo->sortByState($saList,1,$normRepository,$diagnosticService);
        }
        //tri by diagnostic (red,yellow,green,grey)
        elseif ($trierChoice === 'Dia') {
            $saList = $saRepo->sortByState($saList,2,$normRepository,$diagnosticService);
        }

        // Render the view with the form and SA list
        return $this->render('sa_management/technicianList.html.twig', [
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
}
