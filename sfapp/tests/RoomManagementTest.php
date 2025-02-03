<?php

namespace App\Tests;

use App\Entity\Room;
use App\Entity\User;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoomManagementTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        // Initialize client and entity manager
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Create test users
        $userCharge = new User();
        $userCharge->setUsername('charge_user')
            ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_CHARGE']);
        $this->entityManager->persist($userCharge);

        $userTech = new User();
        $userTech->setUsername('tech_user')
            ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_TECH']);
        $this->entityManager->persist($userTech);

        $this->entityManager->flush();
    }

    private function authenticateAsChargeUser(): void
    {
        // Simulate login as charge_user
        $crawler = $this->client->request('GET', '/connexion');
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'charge_user',
            'password' => 'password123',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/charge/salles');
        $this->client->followRedirect();
    }

    public function testRoomManagementPageLoadsSuccessfully()
    {
        $this->authenticateAsChargeUser();

        // Verify that the room management page loads successfully
        $this->client->request('GET', '/charge/salles/ajouter');
        $this->assertResponseIsSuccessful();
    }

    public function testAddRoom()
    {
        $this->authenticateAsChargeUser();

        $crawler = $this->client->request('GET', '/charge/salles/ajouter');
        // Verify the presence of input fields and the submit button
        $this->assertSelectorExists('input[name="add_room[roomName]"]', 'The roomName field is missing.');
        $this->assertSelectorExists('button[type="submit"]', 'The submit button is missing.');
        // Fill and submit the form
        $form = $crawler->selectButton('Enregistrer')->form([
            'add_room[roomName]' => '201',
            'add_room[nbRadiator]' => 2,
            'add_room[nbWindows]' => 4,
        ]);
        $this->client->submit($form);
        // Verify redirection
        $this->assertResponseRedirects('/charge/salles/ajouter');
        $this->client->followRedirect();
        // Verify that the room was added to the database
        $roomRepository = $this->client->getContainer()->get('doctrine')->getRepository(Room::class);
        $room = $roomRepository->findOneBy(['roomName' => '201']);
        $this->assertNotNull($room, 'The room was not added to the database.');
    }

    public function testDeleteRoom()
    {
        $this->authenticateAsChargeUser();

        // Add a room for testing
        $room = new Room();
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $room->setRoomName('202');

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        // Verify that the room was added to the database
        $roomRepository = $this->client->getContainer()->get('doctrine')->getRepository(Room::class);
        $this->assertNotNull($roomRepository->find($room->getId()), 'The room was not added to the database.');

        // Load the rooms page
        $crawler = $this->client->request('GET', '/charge/salles');

        // Locate the "Delete" button associated with the room
        $deleteButton = $crawler->filter('button[data-bs-target="#confirmDeleteModal' . $room->getId() . '"]');

        // Simulate the deletion form submission
        $this->client->request('POST', '/charge/salles/supprimer/' . $room->getId());
        $this->assertResponseRedirects('/charge/salles');

        // Follow the redirection
        $this->client->followRedirect();

        // Verify that the room was deleted from the database
        $deletedRoom = $roomRepository->find($room->getId());
        $this->assertNull($deletedRoom, 'The room was not deleted from the database.');
    }

    public function testDeleteNonExistentRoom()
    {
        $this->authenticateAsChargeUser();

        // Use a non-existent room ID
        $nonExistentId = 9999;

        // Attempt to delete the non-existent room
        $this->client->request('POST', '/charge/salles/supprimer/' . $nonExistentId);

        // Verify that a 404 status code is returned
        $this->assertResponseStatusCodeSame(404);
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
            // Clean up the database after tests
            $this->entityManager->createQuery('DELETE FROM App\\Entity\\Room')->execute();
            $this->entityManager->createQuery('DELETE FROM App\\Entity\\User')->execute();
            $this->entityManager->close();
            $this->entityManager = null;
        }

        parent::tearDown();
    }
}