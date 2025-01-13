<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SaRoomModifyController extends AbstractController
{
    /**
     * @param SaRepository $saRepo
     * @return Response
     * @brief render and manage the association validaton page in the technicien part
     */
    #[Route('/technicien/sa/associer', name: 'app_sa_room_modify')]
    public function index(SaRepository $saRepo): Response
    {
        // Retrieve the list of all SA entities
        $saList = $saRepo->findAll();

        // Retrieve the list of SA entities that are currently available (state 'Disponible')
        $availableSaList = $saRepo->findBy(['state' => 'Disponible']);

        // Render the view and pass the lists of all SA and available SA
        return $this->render('sa_room_modify/index.html.twig', [
            'saList' => $saList,
            'availableSaList' => $availableSaList,
        ]);
    }

    /**
     * @param int $saId
     * @param Request $request
     * @param SaRepository $saRepo
     * @param EntityManagerInterface $em
     * @return Response
     * @brief manages the "changer d'association" button on the association page in the technician part
     */
    #[Route('/technicien/sa/associer/{saId}', name: 'app_sa_room_modify_accepted', methods: ['GET', 'POST'])]
    public function modifyAssociation(
        int $saId,
        Request $request,
        SaRepository $saRepo,
        EntityManagerInterface $em
    ): Response
    {
        // Retrieve the current SA entity based on the provided id
        $currentSa = $saRepo->find($saId);
        if (!$currentSa) {
            throw $this->createNotFoundException('Le système d\'acquisition n\'existe pas');
        }

        // Retrieve the list of available SA entities (state 'Disponible')
        $availableSaList = $saRepo->findBy(['state' => 'Disponible']);

        // Handle form submission (POST request)
        if ($request->isMethod('POST')) {
            // Get the selected SA id from the request
            $selectedSaId = $request->request->get('sa');
            $selectedSa = $saRepo->find($selectedSaId);

            if (!$selectedSa) {
                throw $this->createNotFoundException('Le SA sélectionné n\'existe pas');
            }

            // Retrieve the current room associated with the current SA
            $currentRoom = $currentSa->getRoom();
            if ($currentRoom) {
                // Reset the current room and set the current SA to 'Available'
                $currentRoom->setIdSa(null);
                if ($currentSa->getState() != SAState::Down) {
                    $currentSa->setState(SAState::Available);
                }
                $currentRoom->setSa(null);
                $em->persist($currentRoom);
            }

            // Associate the selected SA with the current room
            $selectedSa->setRoom($currentRoom);
            $selectedSa->setState(SAState::Waiting);
            $currentRoom->setSa($selectedSa);
            $currentRoom->setIdSa($selectedSa->getId());

            // Save the changes to the database
            $em->flush();

            // Redirect to the main page after the association
            return $this->redirectToRoute('app_sa_room_modify');
        }

        // Render the view and pass the lists of all SA and available SA
        return $this->render('sa_room_modify/index.html.twig', [
            'saList' => $saRepo->findAll(),
            'availableSaList' => $availableSaList,
        ]);
    }

    /**
     * @param int $saId
     * @param SaRepository $saRepo
     * @param EntityManagerInterface $em
     * @return Response
     * @brief manages the "valider" button on the association page in the technician part
     */
    #[Route('/technicien/sa/valider/{saId}', name: 'app_sa_set_functional', methods: ['POST'])]
    public function setFunctionalState(
        int $saId,
        SaRepository $saRepo,
        EntityManagerInterface $em
    ): Response
    {
        // Retrieve the SA entity based on the provided id
        $sa = $saRepo->find($saId);
        if (!$sa) {
            throw $this->createNotFoundException('Le système d\'acquisition n\'existe pas');
        }

        // If the SA state is 'Waiting', change it to 'Functional'
        if ($sa->getState() == SAState::Waiting) {
            $sa->setState(SAState::Installed);
            $em->persist($sa);
            $em->flush();
        }

        // Redirect to the main page after updating the state
        return $this->redirectToRoute('app_sa_room_modify');
    }
}
