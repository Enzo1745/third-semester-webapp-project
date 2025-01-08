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

        // Check that the heading is correct
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Page d\'accueil');
    }

    public function testRoomSelectionDisplaysData(): void {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        // Create a Room and a Sa entities and set their properties
        $room = new Room();
        $room->setRoomName('D206');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $entityManager->persist($room);

        $sa = new Sa();
        $sa->setId(time());
        $sa->setState(SAState::Installed);
        $sa->setTemperature(22);
        $sa->setHumidity(50);
        $sa->setCO2(800);
        $sa->setRoom($room);
        $entityManager->persist($sa);

        $room->setIdSa($sa->getId());
        $entityManager->flush();

        // Request the homepage and check the room selection form
        $crawler = $client->request('GET', '/');
        $this->assertSelectorExists('form[name="search_rooms"]');
        $this->assertGreaterThan(0, $crawler->filter('select[name="search_rooms[salle]"] option')->count());

        // Submit the form with the created room ID and check the response
        $form = $crawler->filter('form[name="search_rooms"]')->form([
            'search_rooms[salle]' => $room->getId(),
        ]);
        $client->submit($form);

        // Verify the displayed data is correct
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.value', 'D206');
        $this->assertSelectorTextContains('.temp', '22°');
        $this->assertSelectorTextContains('.humidity', '50%');
        $this->assertSelectorTextContains('.co2', '800');

        $entityManager->clear();
    }
}

