<?php

namespace App\Controller;

use App\Entity\Down;
use App\Entity\Sa;
use App\Form\SaDownType;
use App\Repository\DownRepository;
use App\Repository\Model\SAState;
use App\Repository\SaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SaDownController extends AbstractController
{

    /**
     * @param DownRepository $saDownRepo
     * @param SaRepository $saRepo
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception
     */
    #[Route('/technicien/sa/panne', name: 'app_down')]
    public function addDown(DownRepository $saDownRepo, SaRepository $saRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new down
        $newDown = new Down();

        // Create the form for the new down
        $form = $this->createForm(SaDownType::class, $newDown);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($newDown->getSa() != null && ( // If the selected SA exists
                    $newDown->isCO2() || $newDown->isHumidity() || $newDown->isTemperature() || $newDown->isMicrocontroller()) && // And at least one captor or the microcontroller is selected
                $newDown->getReason() != null) { // And the reason is filled up

                // Set the down date
                $newDown->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

                // Set the selected SA' state to down
                $newDown->getSa()->setState(SAState::Down);

                // Insert the new down in the database
                $entityManager->persist($newDown);
                $entityManager->flush();

                // Return a message when the operation succeed
                $this->addFlash('success', 'Le SA est désormais dysfonctionnel');
                return $this->redirect("#");
            }
        }

        $saList = $saDownRepo->findLastDownSa(); // Get the list of all down sa
        $nbSaFunctionnals = $saRepo->countBySaState(SAState::Installed); // Get the number of functioning SA

        return $this->render('sa_down/sa_down.html.twig', [
            "nbSaFunctionnals" => $nbSaFunctionnals,
            "saForm" => $form->createView(),
            "saDownList" => $saList,
        ]);
    }

    /**
     * @param Sa $sa
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/technicien/sa/panne/{id}', name: 'app_functionnal')]
    public function setFunctionnal(Sa $sa, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Set the selected sa' state to functional
        $sa->setState(SAState::Installed);

        // Update the database
        $entityManager->flush();

        // Display a success message
        $this->addFlash('success', 'Le SA a été réhabilité avec succès.');

        return $this->redirectToRoute('app_down');
    }
}
