<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ConnectionType;
use App\Repository\Model\UserRoles;
use Container2MCmtKJ\getCache_ValidatorExpressionLanguageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;


class ConnexionController extends AbstractController
{

    // Connection page controller
    #[Route('/connexion', name: 'app_connexion')]
    public function index(Request $request, EntityManagerInterface $manager, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ConnectionType::class);
        $form->handleRequest($request);

        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();

        if ($form->isSubmitted()) {
            if ($form->isValid() && $this->CheckLoginIsvalid($username, $password, $userRepository)) {
                $role = $userRepository->findUserRole($username);
                var_dump($role);

                if ($role === null) {
                    return $this->redirectToRoute('app_connexion');
                }

                if ($role == UserRoles::Charge) {
                    return $this->redirectToRoute('app_room_list');
                } elseif ($role == UserRoles::Technicien) {
                    return $this->redirectToRoute('app_technician_sa');
                } else {
                    return $this->redirectToRoute('app_gestion_sa');
                }
            }
        }

        return $this->render('connexion/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function CheckLoginIsvalid($username, $password, UserRepository $userRepository): bool
    {
        if (empty($username) && empty($password)) {
            $this->addFlash('danger', 'Le champ Identifiant et Mot de passe est obligatoire');
        }
        // showing errors when there are empty fields
        elseif (empty($username)) {
            $this->addFlash('danger', 'Le champ Identifiant est obligatoire');
        }

        elseif (empty($password)) {
            $this->addFlash('danger', 'Le champ Mot de passe est obligatoire');
        }
        else
        {
            $truePassword = $userRepository->findUserPassword($username);
            if($truePassword == null or $password != $truePassword){//error popup if password is invalid or not if the username does not exists
                $this->addFlash('danger', 'mot de passe ou identifiant invalide');
            }
            elseif($password == $truePassword){// check passes if the password is the good one based on the username entered
                return true;
            }
        }

        return false ;
    }

}
