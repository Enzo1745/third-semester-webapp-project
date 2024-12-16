<?php

namespace App\Controller;

use App\Form\SearchSaASType;
use App\Form\SerchRoomASType;
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
     * @brief function show a list of sa in the web page
     * @param Request $request
     * @param SaRepository $saRepo
     * @return Response return the web page with the sa list
     */
    #[Route('/technicien/sa', name: 'app_technician_sa')]
    public function techListSa(Request $request, SaRepository $saRepo): Response
    {

        $form = $this->createForm(SearchSaASType::class);
        $form->handleRequest($request);


        $choice = $form->get('filter')->getData();


        if ($choice === 'SADown') {
            $saList = $saRepo->findBy(['state' => SAState::Down]);
        } elseif ($choice === 'SAWaiting') {
            $saList = $saRepo->findBy(['state' => SAState::Waiting]);
        } elseif ($choice === 'SAInstall') {
            $saList = $saRepo->findBy(['state' => SAState::Installed]);
        }
        elseif ($choice === 'SAAvailable') {
            $saList = $saRepo->findBy(['state' => SAState::Available]);
        }
        else {
            $saList = $saRepo->findAll(); // Default: no filter
        }




        return $this->render('gestion_sa/technicianList.html.twig', [
            'form' => $form->createView(),
            "saList" => $saList
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
