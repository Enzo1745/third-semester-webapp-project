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
        $room = new Room();
        $room->setRoomName("D204");
        $this->entityManager->persist($room);

        $sa = new Sa();
        $sa->setState(SaState::Available);
        $sa->setRoom($room);
        $this->entityManager->persist($sa);

        $this->entityManager->flush();

        $this->client->request('GET', '/charge/gestion_sa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.idSa');
        $this->assertSelectorTextContains('.roomName', "D204");
    }

    public function testDisplayListOfSaWithoutSa(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Sa')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();

        $this->client->request('GET', '/charge/gestion_sa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.tab');
        $this->assertSelectorExists('p', 'Aucun système d\'acquisition n\'est disponible.');
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
