<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;

class DisplayRoomsUserTest extends WebTestCase
{
    public function testHomePageAccessibility(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Page d\'accueil');
    }

    public function testRoomSelectionDisplaysData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Create a room D206
        $room = new Room();
        $room->setRoomName("D206");
        $room->setNbRadiator(2);
        $room->setNbWindows(4);


        // Persist the room first to generate its ID
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($room);
        $entityManager->flush();

        // Create a SA with real data for this room
        $sa = new Sa();
        $sa->setId(50);
        $sa->setState(SAState::Installed);
        $sa->setTemperature(22);
        $sa->setHumidity(50);
        $sa->setCO2(800);

        // Persist the SA after setting the room
        $entityManager->persist($sa);
        $entityManager->flush();

        // Link the SA back to the Room with the generated ID
        $sa->setRoom($room);
        $room->setIdSa($sa->getId());

        $entityManager->persist($sa);
        $entityManager->persist($room);
        $entityManager->flush();

        // Submit the search form for room D206
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('search_rooms_salle')->form();
        $form['search_rooms_salle[salle]']->setValue($room->getId());
        $client->submit($form);

        // Ensure the page is accessible and the room data is displayed
        $this->assertResponseIsSuccessful();

        // Check if the room number is correctly displayed
        $this->assertSelectorTextContains('.card-title', 'Numéro de la salle');
        $this->assertSelectorTextContains('.value', 'D206');

        // Check for environmental data: temperature, humidity, CO2
        $this->assertSelectorTextContains('.temp', '22°');
        $this->assertSelectorTextContains('.humidity', '50%');
        $this->assertSelectorTextContains('.co2', '800');

        // Clear the EntityManager to avoid ID collision for subsequent tests
        $entityManager->clear();
    }
}
