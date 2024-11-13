<?php

namespace App\Controller;

use App\Form\SaManagementType;
use App\Repository\Model\SaState;
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
    #[Route('charge/gestion_sa', name: 'app_gestion_sa')]
    public function index(Request $request, EntityManagerInterface $manager, SaRepository $saRepo, RoomRepository $salleRepo): Response
    {
        $nbSa = $saRepo->countBySaState();
        $nbSalle = $salleRepo->countBySaAvailable();


        // Si le nombre de SA disponibles est supérieur à 0, on continue normalement pour afficher le formulaire
        $sa = $saRepo->findOneBy(['etat' => SaState::Available]);

        // Création du formulaire
        $form = $this->createForm(SaManagementType::class, $sa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($sa->getEtat() === SaState::Available and $sa->getSalle()) {
                $sa->setEtat(SaState::Functional);
                $manager->persist($sa);
                $manager->flush();
                $this->addFlash('success', 'Association réussie !');
            }
        }

        $nbSa = $saRepo->countBySaState();

        $nbSalle = $salleRepo->countBySaAvailable();

        if ($nbSalle < 1) {
            return $this->render('gestion_sa/index.html.twig', [
                'form' => null,
                'nbSaDispo' => $nbSa,
                'error_message' => "Aucune salle disponible.",

            ]);
        }

        // Si le nombre de SA disponibles est inférieur ou égal à 0, on bloque n'affiche pas le formulaire
        if ($nbSa < 1) {
            return $this->render('gestion_sa/index.html.twig', [
                'form' => null,
                'nbSaDispo' => 0,
                'error_message' => "Aucun SA disponible."
            ]);
        }

        return $this->render('gestion_sa/index.html.twig', [
            'form' => $form->createView(),
            'nbSaDispo' => $nbSa,
            'error_message' => null,
        ]);
    }
}
