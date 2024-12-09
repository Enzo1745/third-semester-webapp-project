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

    #[Route('/technicien/sa/associer', name: 'app_sa_room_modify')]
    public function index(SaRepository $saRepo): Response
    {

        $saList = $saRepo->findAll();


        $availableSaList = $saRepo->findBy(['state' => 'Disponible']);

        return $this->render('sa_room_modify/index.html.twig', [
            'saList' => $saList,
            'availableSaList' => $availableSaList,
        ]);
    }


    #[Route('/technicien/sa/associer/{saId}', name: 'app_sa_room_modify_accepted', methods: ['GET', 'POST'])]
    public function modifyAssociation(
        int                    $saId,
        Request                $request,
        SaRepository           $saRepo,
        EntityManagerInterface $em
    ): Response
    {

        $currentSa = $saRepo->find($saId);
        if (!$currentSa) {
            throw $this->createNotFoundException('Le système d\'acquisition n\'existe pas');
        }


        $availableSaList = $saRepo->findBy(['state' => 'Disponible']);


        if ($request->isMethod('POST')) {

            $selectedSaId = $request->request->get('sa');
            $selectedSa = $saRepo->find($selectedSaId);

            if (!$selectedSa) {
                throw $this->createNotFoundException('Le SA sélectionné n\'existe pas');
            }


            $currentRoom = $currentSa->getRoom();
            if ($currentRoom) {
                $currentRoom->setIdSa(null);
                $currentSa->setState(SAState::Available);
                $currentRoom->setSa(null);
                $em->persist($currentRoom);
            }


            $selectedSa->setRoom($currentRoom);
            $selectedSa->setState(SAState::Waiting);
            $currentRoom->setSa($selectedSa);
            $currentRoom->setIdSa($selectedSa->getId());


            $em->flush();


            return $this->redirectToRoute('app_sa_room_modify');
        }


        return $this->render('sa_room_modify/index.html.twig', [
            'saList' => $saRepo->findAll(),  // Passer saList à la vue
            'availableSaList' => $availableSaList,
        ]);
    }

    #[Route('/technicien/sa/valider/{saId}', name: 'app_sa_set_functional', methods: ['POST'])]
    public function setFunctionalState(
        int $saId,
        SaRepository $saRepo,
        EntityManagerInterface $em
    ): Response
    {

        $sa = $saRepo->find($saId);
        if (!$sa) {
            throw $this->createNotFoundException('Le système d\'acquisition n\'existe pas');
        }


        if ($sa->getState() == SAState::Waiting) {

            $sa->setState(SAState::Functional);
            $em->persist($sa);
            $em->flush();
        }


        return $this->redirectToRoute('app_sa_room_modify');
    }
}


