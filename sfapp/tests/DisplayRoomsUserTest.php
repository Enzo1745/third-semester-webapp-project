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

        // Persist the room without manually setting the ID
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($room);

        // Create a SA with real data for this room
        $sa = new Sa();
        $sa->setState(SAState::Installed); // The state must be "installed"
        $sa->setTemperature(22); // Real temperature
        $sa->setHumidity(50); // Real humidity
        $sa->setCO2(800); // Real CO2
        $sa->setRoom($room); // Associate SA with the room

        // Persist the SA without manually setting the ID
        $entityManager->persist($sa);

        // Save the data to the database
        $entityManager->flush();

        // Clear the EntityManager to avoid ID collision for subsequent tests
        $entityManager->clear();

        // Submit the search form for room D206
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('search_rooms_salle')->form();
        $crawler = $client->submit($form, ['search_rooms_salle[room]' => 'D206']);

        // Debugging: Dump the HTML of the page to check its structure
        dump($crawler->html()); // This will help you inspect the actual HTML output

        // Ensure the page is accessible and the room data is displayed
        $this->assertResponseIsSuccessful();

        // Check if the room number is correctly displayed
        $this->assertSelectorTextContains('.card-title', 'Numéro de la salle');
        $this->assertSelectorTextContains('.value', 'D206');

        // Check for environmental data: temperature, humidity, CO2
        $this->assertSelectorTextContains('.value.temp', '22°');
        $this->assertSelectorTextContains('.value.humidity', '50%');
        $this->assertSelectorTextContains('.value.co2', '800');

        // Ensure no warning messages or unnecessary text are shown
        $this->assertSelectorNotExists('.alert-warning');
        $this->assertSelectorNotExists('.text-muted');
    }

    public function testEnvironmentalDataDisplayed(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Submit the form with a valid room
        $form = $crawler->selectButton('search_rooms_type_salle')->form([
            'search_rooms_type[salle]' => 'Salle 101',
        ]);
        $crawler = $client->submit($form);

        // Check if CO2, humidity, and temperature values are visible
        $this->assertSelectorExists('.value.temp');
        $this->assertSelectorExists('.value.humidity');
        $this->assertSelectorExists('.value.co2');
    }

    public function testRoomDataIsReadOnly(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Submit the form with a valid room
        $form = $crawler->selectButton('search_rooms_type_salle')->form([
            'search_rooms_type[salle]' => 'Salle 101',
        ]);
        $crawler = $client->submit($form);

        // Ensure the data is displayed but not modifiable
        $this->assertSelectorNotExists('input[name="sa.temperature"]');
        $this->assertSelectorNotExists('input[name="sa.humidity"]');
        $this->assertSelectorNotExists('input[name="sa.CO2"]');
    }

    public function testNonExistingRoomSelection(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Submit the form with an invalid room
        $form = $crawler->selectButton('search_rooms_type_salle')->form([
            'search_rooms_type[salle]' => 'NonExistingRoom',
        ]);
        $crawler = $client->submit($form);

        // Check if no data is displayed
        $this->assertSelectorTextContains('.text-muted', 'Aucune donnée disponible pour cette salle.');
    }
}
