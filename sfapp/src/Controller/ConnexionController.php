<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ConnectionType;
use App\Repository\RoomRepository;
use Container2MCmtKJ\getCache_ValidatorExpressionLanguageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UtilisateurRepository;


class ConnexionController extends AbstractController
{

    // Connection page controller
    #[Route('/connexion', name: 'app_connexion')]
    public function index(Request $request, EntityManagerInterface $manager, UtilisateurRepository $utilisateurRepository): Response
    {
        // Creation the connection form
        $form = $this->createForm(ConnectionType::class);
        $form->handleRequest($request);

        // getting the content int the ID and password fields
        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();




        // script when the user tries to connect
        if ($form->isSubmitted()) {

            // redirection to the temporary succes page when the form is valid and when the Id and pwd fields are not empty
            if ($form->isValid() && $this->CheckLoginIsvalid($username, $password, $utilisateurRepository))
            {
                if($utilisateurRepository->findUserRole($username) == 'charge'){
                    return $this->redirectToRoute('app_room_list');
                }
                elseif($utilisateurRepository->findUserRole($username) == 'technicien'){
                    return $this->redirectToRoute('app_room_management');
                }
                else{
                    return $this->redirectToRoute('app_gestion_sa');
                }
            }
        }

        // Return to the connexion form whith the errors when the connexion fails
        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function CheckLoginIsvalid($username, $password, UtilisateurRepository $utilisateurRepository): bool
    {
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
        else
        {
            $truePassword = $utilisateurRepository->findUserPassword($username);
            if($truePassword == null or $password != $truePassword){
                $this->addFlash('danger', 'mot de passe ou identifiant invalide');
            }
            elseif($password == $truePassword){
                return true;
            }
        }

        return false ;
    }

}
