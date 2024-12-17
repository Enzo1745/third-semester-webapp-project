<?php

namespace App\Controller;

use App\Entity\Sa;
use App\Form\SaManagementType;
use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SaManagementController extends AbstractController
{
    #[Route('/technicien', name: 'app_technician')]
    public function technicianRedirect(): Response
    {
        return $this->redirectToRoute('app_technician_sa');
    }

    #[Route('charge/gestion_sa/associer', name: 'app_gestion_sa_associer')]
    public function index(Request $request, EntityManagerInterface $manager, SaRepository $saRepo, RoomRepository $salleRepo): Response
    {
        // We get the number of availables SA and Rooms.
        $nbSa = $saRepo->countBySaState(SAState::Available);
        $nbSalle = $salleRepo->countBySaAvailable();

        // We select an SA available
        $sa = $saRepo->findOneBy(['state' => SAState::Available]);

        // Form creation
        $form = $this->createForm(SaManagementType::class, $sa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($sa->getState() === SAState::Available && $sa->getRoom()) {
                $sa->setState(SAState::Waiting);
                $room = $sa->getRoom();
                $room->setIdSa($sa->getId()); // Ajoutez cette ligne
                $manager->persist($sa);
                $manager->persist($room); // Ajoutez cette ligne
                $manager->flush();
            }
        }

        // Update of the SA and Rooms number
        $nbSa = $saRepo->countBySaState(SAState::Available);
        $nbSalle = $salleRepo->countBySaAvailable();

        // If the number of room available is less than 1, the form isn't print on the web page and we return an error message.
        if ($nbSalle < 1) {
            return $this->render('gestion_sa/index.html.twig', [
                'form' => null,
                'nbSaDispo' => $nbSa,
                'error_message' => "Aucune salle disponible.",

            ]);
        }

        // If the number of SA available is less than 1, the form isn't print on the web page and we return an error message.
        if ($nbSa < 1) {
            return $this->render('gestion_sa/index.html.twig', [
                'form' => null,
                'nbSaDispo' => 0,
                'error_message' => "Aucun SA disponible."
            ]);
        }

        // The default response return the form, the number of sa and no error message.
        return $this->render('gestion_sa/index.html.twig', [
            'form' => $form->createView(),
            'nbSaDispo' => $nbSa,
            'error_message' => null,
        ]);
    }

    #[Route('technicien/sa/ajouter', name: 'app_add_sa', methods: ['POST'])]
    public function addSa(Request $request, EntityManagerInterface $entityManager): Response
    {

        $saId = $request->request->get('sa_id');


        $newSa = new SA();

        if (!empty($saId)) {

            $existingSa = $entityManager->getRepository(SA::class)->find($saId);
            if ($existingSa) {
                $this->addFlash('error', 'Un système d\'acquisition avec cet ID existe déjà.');
                return $this->redirectToRoute('app_technician_sa');
            }
            $newSa->setId((int)$saId);
        } else {

            $ids = $entityManager->createQueryBuilder()
                ->select('sa.id')
                ->from(SA::class, 'sa')
                ->getQuery()
                ->getArrayResult();


            $allIds = array_column($ids, 'id');
            sort($allIds);


            $newId = 1;
            foreach ($allIds as $existingId) {
                if ($existingId === $newId) {
                    $newId++;
                } elseif ($existingId > $newId) {

                    break;
                }
            }

            $newSa->setId($newId);
        }


        $newSa->setState(SAState::Available);


        $entityManager->persist($newSa);
        $entityManager->flush();

        $this->addFlash('success', 'SA ajouté avec succès.');
        return $this->redirectToRoute('app_technician_sa');

    }






}
