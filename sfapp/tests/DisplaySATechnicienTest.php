<?php

namespace App\Tests;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * test od list of sa on the technicien web page
 */


class DisplaySATechnicienTest extends WebTestCase
{
    private $client;
    private $entityManager;

    //start test
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

   // test get route
    public function testListRoute(): void
    {
        $this->client->request('GET', '/technicien/sa');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    //test show sa with functionnal state
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

    //test show sa with non functionnal state
    public function testDisplayNonFunctionalSANoLinkRoom(): void
    {
        $sa = new Sa();
        $sa->setState(SAState::Down)
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

    //test show sa with waiting state
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

    //end test
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
