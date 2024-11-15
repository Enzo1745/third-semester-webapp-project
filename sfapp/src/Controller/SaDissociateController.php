<?php

namespace App\Controller;

use App\Repository\Model\SaState;
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
        $sa = $saRepo->find($id);
        if ($sa AND $sa->getRoom() != null) {
            $sa->setRoom(null);
            $sa->setState(SaState::Available);
            $entityManager->persist($sa);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestion_sa');
    }
}
