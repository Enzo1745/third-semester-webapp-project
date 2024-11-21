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

            if (empty($username) && empty($password)) {
                $this->addFlash('danger', 'Le champ Identifiant et Mot de passe est obligatoire');
            }
            // showing errors whent there are empty fields
            elseif (empty($username)) {
                $this->addFlash('danger', 'Le champ Identifiant est obligatoire');
            }

            elseif (empty($password)) {
                $this->addFlash('danger', 'Le champ Mot de passe est obligatoire');
            }

            // redirection to the temporary succes page when the form is valid and when the Id and pwd fields are not empty
            if ($form->isValid() && !empty($username) && !empty($password)) {
                return $this->redirectToRoute('app_room_list');
            }
        }

        // Retrun to the connetion form whith the errors when the connection fails
        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
