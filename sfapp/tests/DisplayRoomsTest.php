<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class DisplayRoomsTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');


    }

    public function testSearchingDetailsOfAssociatedSAWithData(): void
    {
        // Créer et persister la Room
        $room = new Room();
        $room->setRoomName("D101");
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        // Créer le SA et l'associer à la Room
        $sa = new Sa();
        $sa->setState(SAState::Functional);
        $sa->setTemperature(25);
        $sa->setHumidity(60);
        $sa->setCO2(1200);
        $sa->setRoom($room); // Établir la relation
        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        // Requête de la page
        $crawler = $this->client->request('GET', '/charge/salles/liste/D101');

        $this->assertResponseIsSuccessful();

        // Vérifier le titre de la page
        $this->assertSelectorTextContains('h1.h2.fw-bold', 'Détails de la salle D101');


        //$this->assertSelectorTextContains('.detail-block .value.temp', '25°');
       // $this->assertSelectorTextContains('.detail-block .value.humidity', '60%');
        //$this->assertSelectorTextContains('.detail-block .value.co2', '1200');
    }

    public function testSearchingDetailsOfSalleWithoutSA(): void
    {
        // Créez la Room sans SA
        $room = new Room();
        $room->setRoomName("D303");
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        // Effectuez la requête
        $crawler = $this->client->request('GET', '/charge/salles/liste/D303');

        $this->assertResponseIsSuccessful();


        //$this->assertSelectorTextContains('.salle-num', 'D303');
        //$this->assertSelectorTextContains('.salle-details', 'Aucune donnée');
    }
}