<?php

namespace App\Controller;

use App\Entity\Sa;
use App\Form\GestionSaType;
use App\Repository\Model\EtatSA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GestionSaController extends AbstractController
{
    #[Route('charge/gestion_sa', name: 'app_gestion_sa')]
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        $sa = new Sa();

        $form = $this->createForm(GestionSaType::class, $sa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sa->setEtat(EtatSA::Fonctionnel);
            $manager->persist($sa);
            $manager->flush();
        }
        return $this->render('gestion_sa/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
