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

        // Create database schema
        $this->createSchema();
    }

    private function createSchema(): void
    {
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if (!empty($metadatas)) {
            $tool = new SchemaTool($this->entityManager);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        }
    }

    public function testSearchingDetailsOfAssociatedSAWithData(): void
    {
        // Create and persist SA first to get its ID
        $sa = new Sa();
        $sa->setState(SAState::Functional);
        $sa->setTemperature(25);
        $sa->setHumidity(60);
        $sa->setCO2(1200);
        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        // Create Room and associate with SA
        $room = new Room();
        $room->setRoomName("D101");
        $room->setIdSA($sa->getId());
        $this->entityManager->persist($room);

        // Update SA with Room reference
        $sa->setRoom($room);
        $this->entityManager->persist($sa);

        $this->entityManager->flush();
        $this->entityManager->refresh($sa);
        $this->entityManager->refresh($room);

        // Request the page
        $crawler = $this->client->request('GET', '/charge/salles/liste/D101');

        $this->assertResponseIsSuccessful();

        // Test the room name
        $this->assertSelectorTextContains('.salle-num', 'D101');

        // Test the SA data using the correct selectors
        $this->assertSelectorTextContains('.detail-block .value.temp', '25°');
        $this->assertSelectorTextContains('.detail-block .value.humidity', '60%');
        $this->assertSelectorTextContains('.detail-block .value.co2', '1200');
    }

    public function testSearchingDetailsOfSalleWithoutSA(): void
    {
        // Create Room without SA
        $room = new Room();
        $room->setRoomName("D303");
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        // Request the page
        $crawler = $this->client->request('GET', '/charge/salles/liste/D303');

        $this->assertResponseIsSuccessful();

        // Test the room name
        $this->assertSelectorTextContains('.salle-num', 'D303');

        // Test for "Aucune donnée" message
        $this->assertSelectorTextContains('.salle-details', 'Aucune donnée');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}