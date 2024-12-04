<?php

namespace App\Tests;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DisplaySATechnicienTest extends WebTestCase
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
        $this->client->request('GET', '/technicien/sa');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDisplayFunctionalSA(): void
    {
        $room = new Room();
        $room->setRoomName("D005")->setNbRadiator(2)->setNbWindows(3);

        $sa = new Sa();
        $sa->setState(SAState::Functional)
            ->setRoom($room)
            ->setTemperature(20)
            ->setHumidity(50)
            ->setCO2(400);

        $this->entityManager->persist($room);
        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/technicien/sa');

        $this->assertSelectorTextContains('table', 'D005');
        $this->assertSelectorTextContains('table', 'Fonctionnel');
        $this->assertSelectorExists('a:contains("Voir détails")');
    }

    public function testDisplayNonFunctionalSANoLinkRoom(): void
    {
        $sa = new Sa();
        $sa->setState(SAState::Breakdown)
            ->setRoom(null)
            ->setTemperature(18)
            ->setHumidity(60)
            ->setCO2(500);

        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/technicien/sa');

        $this->assertSelectorTextContains('table', 'En panne');
        $this->assertSelectorTextContains('table', 'Aucune');
        $this->assertSelectorNotExists('a:contains("Voir détails")');
    }

    public function testDisplayWaitingSA(): void
    {
        $sa = new Sa();
        $sa->setState(SAState::Waiting)
            ->setRoom(null)
            ->setTemperature(19)
            ->setHumidity(45)
            ->setCO2(410);

        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/technicien/sa');

        $this->assertSelectorTextContains('table', 'En attente');
        $this->assertSelectorTextContains('table', 'Aucune');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up the database after each test
        $this->entityManager->createQuery('DELETE FROM App\Entity\Sa')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
