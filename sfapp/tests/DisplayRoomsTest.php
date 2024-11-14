<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Salle;

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
        $salle = new Salle();
        $salle->setNumSalle('SalleTest');
        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        $this->client->request('GET', '/charge/salles/liste');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.salles-item');
        $this->assertSelectorTextContains('.salles-item', 'SalleTest');
    }

    public function testDisplayListOfRoomsWithoutRooms(): void
    {
        // Nettoyer la base de données
        $this->entityManager->createQuery('DELETE FROM App\Entity\Salle')->execute();

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