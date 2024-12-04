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
    #[Route('/technicien/sa/panne', name: 'app_down')]
    public function addDown(DownRepository $saDownRepo, SaRepository $saRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $newDown = new Down();

        $form = $this->createForm(SaDownType::class, $newDown, [

        ]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            if ($newDown->getSa() != null and $newDown->isCO2() or $newDown->isHumidity() or $newDown->isTemperature() or $newDown->isMicrocontroller() and $newDown->getReason() != null) {
                $newDown->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

                $newDown->getSa()->setState(SAState::Down);

                $entityManager->persist($newDown);
                $entityManager->flush();

                $this->addFlash('success', 'Le SA est désormais dysfonctionnel');

                return $this->redirect("#");
            }
        }

        $saList = $saDownRepo->findAllDownSa();
        $nbSaFunctionnals = $saRepo->countBySaState(SAState::Functional);

        if ($saList != null)
        {
            return $this->render('sa_down/sa_down.html.twig', [
                "nbSaFunctionnals" => $nbSaFunctionnals,
                "saForm" => $form->createView(),
                "saDownList" => $saList,
            ]);
        }

        else{
            return $this->render('sa_down/sa_down.html.twig', [
                "nbSaFunctionnals" => $nbSaFunctionnals,
                "saForm" => $form->createView(),
                "saDownList" => null,
            ]);
        }
    }

    #[Route('/technicien/sa/panne/{id}', name: 'app_functionnal')]
    public function setFunctionnal(Sa $sa, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sa->setState(SAState::Functional);

        $entityManager->flush();

        $this->addFlash('success', 'Le SA a été réhabilité avec succès.');

        return $this->redirectToRoute('app_down');
    }
}
