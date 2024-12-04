<?php

namespace App\Controller;

use App\Entity\Down;
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
    #[Route('/technicien/sa/panne', name: 'app_test_data_local')]
    public function addDown(DownRepository $saRepo, Request $request, EntityManagerInterface $entityManager): Response
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

                return $this->redirect("#");
            }
        }

        return $this->render('sa_down/sa_down.html.twig', [
            "saForm" => $form->createView(),
        ]);
    }
}
