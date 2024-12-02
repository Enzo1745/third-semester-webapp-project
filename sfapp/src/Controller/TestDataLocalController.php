<?php

namespace App\Controller;

use App\Form\TestDataLocalType;
use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TestDataLocalController extends AbstractController
{
    #[Route('/technicien/sa/test', name: 'app_test_data_local')]
    public function listSA(SaRepository $saRepo, Request $request): Response
    {
        // Récupérer les identifiants des entités Sa
        $saIds = $saRepo->findAllIds();

        $form = $this->createForm(TestDataLocalType::class, null, [
            'sa_ids' => $saIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // Traiter la soumission du formulaire
        }

        return $this->render('test_data_local/test_data.html.twig', [
            "saForm" => $form->createView(),
        ]);
    }
}
