<?php

namespace App\Controller;

use App\Form\ConnectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConnexionController extends AbstractController
{

    // Connection page controller
    #[Route('/connexion', name: 'app_connexion')]
    public function index(Request $request): Response
    {
        // Creation the connection form
        $form = $this->createForm(ConnectionType::class);
        $form->handleRequest($request);

        // getting the content int the ID and password fields
        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();

        // script when the user tries to connect
        if ($form->isSubmitted()) {
            // showing errors whent there are empty fields
            if (empty($username)) {
                $this->addFlash('errorUser', 'Le champ Identifiant est obligatoire');
            }

            if (empty($password)) {
                $this->addFlash('errorPWD', 'Le champ Mot de passe est obligatoire');
            }

            // redirection to the temporary succes page when the form is valid and when the Id and pwd fields are not empty
            if ($form->isValid() && !empty($username) && !empty($password)) {
                return $this->redirectToRoute('app_connection_sucess');
            }
        }

        // Retrun to the connetion form whith the errors when the connection fails
        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // temporary succes page controller
    #[Route('/connexion/succes', name: 'app_connection_sucess')]
    public function connectionReussie(): Response
    {
        return $this->render('connexion/succes.html.twig');
    }
}
