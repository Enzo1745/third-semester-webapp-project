<?php

namespace App\Controller;

use App\Form\ConnectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConnexionController extends AbstractController
{
    #[Route('/connexion', name: 'app_connection')]
    public function index(Request $request): Response
    {
        // Créer et traiter le formulaire
        $form = $this->createForm(ConnectionType::class);
        $form->handleRequest($request);

        $identifiant = $form->get('identifiant')->getData();
        $password = $form->get('motdepasse')->getData();

        // Vérification de la soumission et des erreurs avant la redirection
        if ($form->isSubmitted()) {
            // Validation des champs 'identifiant' et 'motdepasse'
            if (empty($identifiant)) {
                $this->addFlash('errorID', 'Le champ Identifiant est obligatoire');
            }

            if (empty($password)) {
                $this->addFlash('errorMDP', 'Le champ Mot de passe est obligatoire');
            }

            // Vérifier si le formulaire est valide et que tous les champs sont remplis
            if ($form->isValid() && !empty($identifiant) && !empty($password)) {
                return $this->redirectToRoute('app_connection_sucess');
            }
        }

        // Retourner le formulaire avec les erreurs affichées
        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/connexion/done', name: 'app_connection_sucess')]
    public function connectionReussie(): Response
    {
        return $this->render('connexion/succes.html.twig');
    }
}
