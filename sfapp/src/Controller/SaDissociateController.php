<?php

namespace App\Controller;

use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SaDissociateController extends AbstractController
{
    #[Route('/sa/dissociate/{id}', name: 'app_sa_dissociate', methods: ['POST'])]
    public function dissociate(int $id, SaRepository $saRepo, EntityManagerInterface $entityManager): Response
    {
        // Get the sa with the $id
        $sa = $saRepo->find($id);

        if ($sa AND $sa->getRoom() != null) { // Test that the sa has a room associated

            // Dissociate the room and reset the sa's state
            $sa->setRoom(null);
            $sa->setState(SAState::Available);
            $entityManager->persist($sa);
            $entityManager->flush();
        }
        // Redirect to the sa list page
        return $this->redirectToRoute('app_gestion_sa');
    }
}
