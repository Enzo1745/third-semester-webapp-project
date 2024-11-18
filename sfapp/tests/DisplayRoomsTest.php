<?php

namespace App\Tests;

use App\Entity\Sa;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Room;

class DisplayRoomsTest extends WebTestCase
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

    public function testListeRoute(): void
    {
        $this->client->request('GET', '/charge/salles/liste');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDisplayListOfRoomsWithRooms(): void
    {
        // Créer une salle de test
        $salle = new Room();
        $salle->setRoomName('SalleTest');
        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        $this->client->request('GET', '/charge/salles/liste');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.rooms-item');
        $this->assertSelectorTextContains('.rooms-item', 'Salle Test');
    }

    public function testDisplayListOfRoomsWithoutRooms(): void
    {
        // Dissociate or delete all Sa entities associated with rooms
        $saRepository = $this->entityManager->getRepository(Sa::class);
        $saEntities = $saRepository->findAll();

        foreach ($saEntities as $sa) {
            $sa->setRoom(null); // Dissociate the room
            $this->entityManager->persist($sa);
        }
        $this->entityManager->flush();

        // Now delete all Room entities
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();

        $this->client->request('GET', '/charge/salles/liste');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.salles-item');
        $this->assertSelectorExists('p', 'Aucune salle n\'est disponible.');
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