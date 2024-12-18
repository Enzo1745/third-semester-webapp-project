<?php

namespace App\Tests;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SaState;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DissociateSaTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {

        // Create the client
        $this->client = static::createClient();

        // Get the entityManager
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testPageIsAccessibleAndDataIsPresent(): void
    {
        // Create a room
        $room = new Room();
        $room->setRoomName('D204');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        // Create an available SA and associate it to the room
        $sa = new Sa();
        $sa->setState(SaState::Available);
        $sa->setRoom($room);
        $this->entityManager->persist($sa);

        $this->entityManager->flush();

        // Get the ID of the created Sa entity
        $saId = $sa->getId();

        // GET request to the desired page
        $crawler = $this->client->request('GET', '/charge/sa_management');

        $this->assertResponseIsSuccessful();

        // Check if the table exists
        $this->assertSelectorExists('table');

        // Check if a row with the room 'D204' is present
        $this->assertSelectorTextContains('td.roomName', 'D204');

        // Check if the button 'Dissocier' is present in the corresponding row with the sa's is
        $this->assertSelectorExists(
            'button[data-bs-target="#confirmDeleteModal' . $saId . '"]',
            'Le bouton de dissociation est introuvable.'
        );

// Verify that the form exists inside the modal
        $this->assertSelectorExists(
            'form[action="/sa/dissociate/' . $saId . '"]',
            'Le formulaire de dissociation est introuvable.'
        );



    }

    public function testDataIsSuppressed(): void
    {
        // Set up the room and sa
        $room = new Room();
        $room->setRoomName('D204');
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        $sa = new Sa();
        $sa->setState(SaState::Available);
        $sa->setRoom($room);  // Associate the SA with the room
        $this->entityManager->persist($sa);

        $this->entityManager->flush();


        // GET request to the desired page
        $crawler = $this->client->request('GET', '/charge/sa_management');
        $this->assertResponseIsSuccessful();


        // Locate the modal form using its ID and submit it
        $form = $crawler->filter('form[action="/sa/dissociate/' . $sa->getId() . '"]')->form();
        $this->client->submit($form);

// Clear the entity manager and check the dissociation
        $this->entityManager->clear();
        $refetchedSa = $this->entityManager->find(Sa::class, $sa->getId());
        $this->assertNull($refetchedSa->getRoom(), 'La dissociation a échoué.');


        $this->entityManager->clear();  // Clear the entity manager
        $refetchedSa = $this->entityManager->find(Sa::class, $sa->getId());  // Refetch the sa entity by its id

        // After submitting the form, the SA should no longer be associated with the room
        $this->assertNull($refetchedSa->getRoom());  // Check if the room association is null

        // Verify that the sa is still in the database (if it was only dissociated, not deleted)
        $this->assertNotNull($refetchedSa);


    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Close the entityManager
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
