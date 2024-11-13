<?php

namespace App\Tests\Controller;

use App\Repository\Model\SaState;
use App\Repository\SaRepository;
use App\Repository\RoomRepository;
use App\Entity\Sa;
use App\Entity\Salle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SaManagementControllerTest extends WebTestCase
{
    /**public function testAssociateSaAndRoomsAvailable()
    {
        $client = static::createClient();

        // Créer une salle et un SA simulés
        $salle = new Salle();
        $salle->setNomSalle('Salle 1');

        $sa = new Sa();

        // Check if the new sa and salle aren't associated with anything
        $this->assertEquals($salle->getSa(), null);
        $this->assertEquals($sa->getSalle(), null);

        $salle->setSa($sa);

        $sa->setEtat(SaState::Available);
        $sa->setSalle($salle);

        // Check if the new sa and salle aren't associated with anything
        $this->assertEquals($salle->getSa(), $sa);
        $this->assertEquals($sa->getSalle(), $salle);

        /**
        // Associer un SA à une salle
        $crawler = $client->request('GET', '/charge/gestion_sa');
        dump($crawler->html());

        // Simuler la réponse de la page en cas de succès
        $this->assertResponseIsSuccessful(); // Vérifie que la réponse est bien 200 OK

        // Vérifier que le formulaire est bien rendu
        $this->assertSelectorExists('form');

        // Tester si le message d'erreur s'affiche lorsque la salle est indisponible
        $roomRepo->method('countBySaAvailable')->willReturn(0);
        $client->request('GET', '/charge/gestion_sa');
        $this->assertSelectorTextContains('.error_message', 'Aucune salle disponible.');
    } */

    public function testPageHassoul(){
        $client = static::createClient();

        $crawler = $client->request('GET', '/charge/gestion_sa');

        $this->assertResponseIsSuccessful();

        $salle = new Salle();
        $salle->setNomSalle('Salle test');

        $form = $crawler->selectButton('Associer')->form();


        $form['saManagement[nom_salle]'] = $salle;

        $client->submit($form);

        $this->assertEquals($salle->getSa(), !null);

    }




/**
    public function testNoSaAvailable()
    {
        $client = static::createClient();

        // Simuler l'absence de SA disponible
        $saRepo = $this->createMock(SaRepository::class);
        $roomRepo = $this->createMock(RoomRepository::class);
        $saRepo->method('countBySaState')->willReturn(0); // Aucun SA disponible
        $roomRepo->method('countBySaAvailable')->willReturn(1); // 1 salle disponible

        // Effectuer la requête GET
        $client->request('GET', '/charge/gestion_sa');

        // Vérifier que le message d'erreur est affiché : "Aucun SA disponible."
        $this->assertSelectorTextContains('.error_message', 'Aucun SA disponible.');
    }*/
}
