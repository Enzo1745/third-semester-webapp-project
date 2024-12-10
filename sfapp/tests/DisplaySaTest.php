<?php

namespace App\Tests;

use App\Repository\Model\SaState;
use App\Entity\Room;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Sa;

class DisplaySaTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testListRoute(): void
    {
        $this->client->request('GET', '/charge/gestion_sa');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDisplayListOfSaWithSa(): void
    {
        // Create test data
        $room = new Room();
        $room->setRoomName("D204");
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        $sa = new Sa();
        $sa->setState(SaState::Available);
        $sa->setRoom($room);
        $this->entityManager->persist($sa);

        $this->entityManager->flush();

        // Make request
        $crawler = $this->client->request('GET', '/charge/gestion_sa');

        // Assert response and content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.sa-table', 'Table should be present');
        $this->assertSelectorExists('.idSa', 'SA ID should be present');
        $this->assertSelectorExists('td.roomName', 'Room name cell should exist');

    }

    public function testDisplayListOfSaWithoutSa(): void
    {
        // Clear existing data
        $this->entityManager->createQuery('DELETE FROM App\Entity\Sa')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();

        // Make request
        $crawler = $this->client->request('GET', '/charge/gestion_sa');

        // Assert response and content
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.sa-table', 'Table should not be present');
        $this->assertSelectorExists('.alert-info', 'Alert message should be present');
        $this->assertSelectorTextContains('.alert-info', 'Aucun système d\'acquisition n\'est disponible.');
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
        $this->client = null;
        parent::tearDown();
    }
}