<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class SaManagementTest extends WebTestCase
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

    public function testPageIsAccessibleAndFormIsPresent(): void
    {
        // Create an available SA
        $sa = new Sa();
        $sa->setState(SAState::Available);
        $this->entityManager->persist($sa);

        // Create a room without sa
        $room = new Room();
        $room->setRoomName('Salle Test');
        $this->entityManager->persist($room);

        $this->entityManager->flush();

        // GET request to the desired page
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('form[name="form_name"]');

        // Check the presence of the button 'Associer'
        $this->assertSelectorExists('button:contains("Associer")');
    }

    public function testPageWithoutAvailableRoom(): void
    {
        // Create an available SA
        $saAvailable = new Sa();
        $saAvailable->setState(SAState::Available);
        $this->entityManager->persist($saAvailable);

        // Create a room and associate it to a functional SA (Unavailable room)
        $room = new Room();
        $room->setRoomName('Room D204');
        $this->entityManager->persist($room);

        $saFunctional = new Sa();
        $saFunctional->setState(SAState::Functional);
        $saFunctional->setRoom($room);
        $this->entityManager->persist($saFunctional);

        $this->entityManager->flush();
        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        $this->assertResponseIsSuccessful();

        // Check if the form exists
        $this->assertSelectorNotExists('form[name="form_name"]');

        // Check if the message "Aucune salle disponible." is displayed.
        $this->assertSelectorTextContains('.addForm', 'Aucune salle disponible.');
    }

    public function testPageWithoutAvailableSa(): void
    {
        // Create an available room
        $room = new Room();
        $room->setRoomName('Salle Disponible');
        $this->entityManager->persist($room);

        // Create an unavailable SA (state 'Functional')
        $saNotAvailable = new Sa();
        $saNotAvailable->setState(SAState::Functional);
        $this->entityManager->persist($saNotAvailable);

        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/charge/gestion_sa/associer');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorNotExists('form[name="form_name"]');

        // Check if the message "Aucun SA disponible." is displayed
        $this->assertSelectorTextContains('.addForm', 'Aucun SA disponible.');
    }

    public function testSaAssociationAndAvailableSaCount(): void
    {
        $client = $this->client;
        $entityManager = $this->entityManager;

        $sa = new Sa();
        $sa->setState(SAState::Available);
        $entityManager->persist($sa);

        $room = new Room();
        $room->setRoomName('Salle Test');
        $entityManager->persist($room);

        $entityManager->flush();

        // Display the form and submit data
        $crawler = $client->request('GET', '/charge/gestion_sa/associer');
        $form = $crawler->selectButton('Associer')->form([
            'sa_management[room]' => $room->getId(),
        ]);

        $client->submit($form);

        $entityManager->clear();

        // // Reload entities
        $sa = $entityManager->getRepository(Sa::class)->find($sa->getId());

        // Check that the SA is correctly associated with the room
        $this->assertSame($room->getId(), $sa->getRoom()->getId());

        // Check that the sa's state is now 'Functional'
        $this->assertSame(SAState::Functional, $sa->getState());

        // Check if the number of available SA is decreased
        $nbSaAvailable = $entityManager->getRepository(Sa::class)->count(['state' => SAState::Available]);
        $this->assertSame(0, $nbSaAvailable);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Close the entityManager
        $this->entityManager->close();
        $this->entityManager = null;
    }

}