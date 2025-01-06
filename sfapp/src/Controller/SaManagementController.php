<?php

namespace App\Controller;

use App\Entity\Down;
use App\Entity\Sa;
use App\Form\DownHistoryType;
use App\Form\SaDownType;
use App\Form\SaManagementType;
use App\Repository\DownRepository;
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

    #[Route('charge/gestion_sa/associer', name: 'app_sa_associate')]
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
            return $this->render('sa_management/index.html.twig', [
                'form' => null,
                'nbSaDispo' => $nbSa,
                'error_message' => "Aucune salle disponible.",

            ]);
        }

        // If the number of SA available is less than 1, the form isn't print on the web page and we return an error message.
        if ($nbSa < 1) {
            return $this->render('sa_management/index.html.twig', [
                'form' => null,
                'nbSaDispo' => 0,
                'error_message' => "Aucun SA disponible."
            ]);
        }

        // The default response return the form, the number of sa and no error message.
        return $this->render('sa_management/index.html.twig', [
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

    #[Route('/charge/gestion_sa/dissocier/{id}', name: 'app_sa_dissociate', methods: ['POST'])]
    public function dissociate(int $id, SaRepository $saRepo, EntityManagerInterface $entityManager): Response
    {
        // Get the sa with the $id
        $sa = $saRepo->find($id);

        if ($sa AND $sa->getRoom() != null) { // Test that the sa has a room associated
            // Dissociate the room and reset the sa's state

            $room = $sa->getRoom();
            $room->setIdSa(null);  // Explicitly set idSa to null
            $sa->setRoom(null);    // This will handle both sides of the relationship
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
            $entityManager->flush();
        }
        // Redirect to the sa list page
        return $this->redirectToRoute('app_sa_management');
    }

    /**
     * @param DownRepository $saDownRepo
     * @param SaRepository $saRepo
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception
     */
    #[Route('/technicien/sa/panne', name: 'app_down')]
    public function addDown(DownRepository $saDownRepo, SaRepository $saRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new down
        $newDown = new Down();

        // Create the form for the new down
        $form = $this->createForm(SaDownType::class, $newDown);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($newDown->getSa() != null && ( // If the selected SA exists
                    $newDown->isCO2() || $newDown->isHumidity() || $newDown->isTemperature() || $newDown->isMicrocontroller()) && // And at least one captor or the microcontroller is selected
                $newDown->getReason() != null) { // And the reason is filled up

                // Set the down date
                $newDown->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

                // Set the selected SA' state to down
                $newDown->getSa()->setState(SAState::Down);

                // Insert the new down in the database
                $entityManager->persist($newDown);
                $entityManager->flush();

                // Return a message when the operation succeed
                $this->addFlash('success', 'Le SA est désormais dysfonctionnel');
                return $this->redirect("#");
            }
        }

        $saList = $saDownRepo->findLastDownSa(); // Get the list of all down sa
        $nbSaFunctionnals = $saRepo->countBySaState(SAState::Installed); // Get the number of functioning SA

        return $this->render('sa_down/sa_down.html.twig', [
            "nbSaFunctionnals" => $nbSaFunctionnals,
            "saForm" => $form->createView(),
            "saDownList" => $saList,
        ]);
    }

    /**
     * @param Sa $sa
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
   /* #[Route('/technicien/sa/panne/{id}', name: 'app_functionnal')]
    public function setFunctionnal(Sa $sa, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Set the selected sa' state to functional
        $sa->setState(SAState::Available);

        // Update the database
        $entityManager->flush();

        // Display a success message
        $this->addFlash('success', 'Le SA a été réhabilité avec succès.');

        return $this->redirectToRoute('app_down');
    }*/

    #[Route('/technicien/sa/panne/historique', name: 'app_history')]
    public function showhistory(DownRepository $downRepository, Request $request): Response
    {
        // Création du formulaire
        $form = $this->createForm(DownHistoryType::class);
        $form->handleRequest($request);

        // Récupérer le SA sélectionné via le formulaire
        $sa = $form->get('filtrer')->getData();

        // Initialiser la requête de base pour récupérer les pannes
        $queryBuilder = $downRepository->createQueryBuilder('d')
            ->leftJoin('d.sa', 'sa')
            ->orderBy('d.date', 'DESC'); // Tri par date décroissante par défaut

        // Appliquer les filtres si les valeurs sont renseignées
        if ($sa) {
            $queryBuilder->andWhere('d.sa = :sa')
                ->setParameter('sa', $sa);
        }

        $dateBeg = $form->get('dateBeg')->getData();
        $dateEnd = $form->get('dateEnd')->getData();

        if ($dateBeg) {
            $queryBuilder->andWhere('d.date >= :dateBeg')
                ->setParameter('dateBeg', $dateBeg->format('Y-m-d'));
        }

        if ($dateEnd) {
            // Pour inclure toute la journée, mettez l'heure à 23:59:59
            $dateEnd->setTime(23, 59, 59);
            $queryBuilder->andWhere('d.date <= :dateEnd')
                ->setParameter('dateEnd', $dateEnd->format('Y-m-d H:i:s'));
        }

        // Exécuter la requête pour obtenir les résultats filtrés
        $down = $queryBuilder->getQuery()->getResult();

        // Calculer le nombre de pannes pour le SA sélectionné (si un SA est sélectionné)
        $nbPannes = 0;
        if ($sa) {
            $nbPannes = count($downRepository->findBy(['sa' => $sa]));
        }

        // Retourner les résultats à la vue
        return $this->render('sa_down/sa_history.html.twig', [
            'down' => $down,
            'form' => $form->createView(),
            'nbPannes' => $nbPannes, // Passer le nombre de pannes à la vue
            'sa' => $sa // Passer l'objet SA à la vue pour l'affichage
        ]);
    }
}
