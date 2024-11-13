<?php

namespace App\Controller;

use App\Form\ConnectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConnexionController extends AbstractController
{

    //gestion de la page de connexion
    #[Route('/connexion', name: 'app_connection')]
    public function index(Request $request): Response
    {
        // Creation du formulaire de connexion
        $form = $this->createForm(ConnectionType::class);
        $form->handleRequest($request);

        // obtention du contenu des sections identifiant et mod de passe du formulaire
        $identifiant = $form->get('identifiant')->getData();
        $password = $form->get('motdepasse')->getData();

        // Code dans le cas ou l'utilisateur essaye de se connecter
        if ($form->isSubmitted()) {
            // Affichage des errreurs si les champs identifiants ou mit de passe sont vides
            if (empty($identifiant)) {
                $this->addFlash('errorID', 'Le champ Identifiant est obligatoire');
            }

            if (empty($password)) {
                $this->addFlash('errorMDP', 'Le champ Mot de passe est obligatoire');
            }

            // redirection vers la page de succès si le formulaire est valide et si les champs identifiants et mot de passe sont remplis
            if ($form->isValid() && !empty($identifiant) && !empty($password)) {
                return $this->redirectToRoute('app_connection_sucess');
            }
        }

        // Retourner le formulaire avec les erreurs affichées s'il y en a
        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // gestion de la page de succes
    #[Route('/connexion/done', name: 'app_connection_sucess')]
    public function connectionReussie(): Response
    {
        return $this->render('connexion/succes.html.twig');
    }
}
