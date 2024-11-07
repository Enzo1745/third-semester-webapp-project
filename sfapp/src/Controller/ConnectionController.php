<?php

namespace App\Controller;

use App\Form\ConnectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConnectionController extends AbstractController
{
    #[Route('/connection', name: 'app_connection')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ConnectionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->redirectToRoute('app_connection_sucess');
        }
        return $this->render('connection/index.html.twig', [
            'form' => $form->createView(),
        ]);
}

    #[Route('/connection/done', name: 'app_connection_sucess')]
    public function connectionReussie(): Response
    {
        return $this->render('connection/succes.html.twig');
    }
}
