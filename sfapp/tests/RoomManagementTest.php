<?php

namespace App\Tests;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoomManagementTest extends WebTestCase
{
    public function testRoomManagementPageLoadsSuccessfully()
    {
        $client = static::createClient();
        $client->request('GET', '/charge/salles/ajouter');

        $this->assertResponseIsSuccessful();
    }

    /*
     * Test if a room can be successfully added
     */
    public function testAddRoom()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/charge/salles/ajouter');




        // Fill and submit the form using the correct field names
        $form = $crawler->selectButton('Enregistrer')->form([
            'add_room[roomName]' => '201',
            'add_room[nbRadiator]' => 2,
            'add_room[nbWindows]' => 4,
        ]);
        $client->submit($form);

        // Verify redirection
        $this->assertResponseRedirects('/charge/salles/ajouter');
        $client->followRedirect();

        // Verify the room is added to the database
        $roomRepository = static::getContainer()->get(RoomRepository::class);
        $room = $roomRepository->findOneBy(['roomName' => '201']);
        $this->assertNotNull($room, 'La salle n\'a pas été ajoutée dans la base de données.');
    }






    /*
     * Test if a room can be successfully deleted
     */
    public function testDeleteRoom()
    {
        $client = static::createClient();

        // Ajouter une salle pour le test
        $room = new Room();
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $room->setRoomName('202');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($room);
        $entityManager->flush();

        // Vérifier que la salle est bien ajoutée
        $roomRepository = static::getContainer()->get(RoomRepository::class);
        $this->assertNotNull($roomRepository->find($room->getId()), 'La salle n\'a pas été ajoutée dans la base de données.');

        // Charger la page des salles
        $crawler = $client->request('GET', '/charge/salles');

        // Localiser le bouton "Supprimer" associé à la salle
        $deleteButton = $crawler->filter('button[data-bs-target="#confirmDeleteModal' . $room->getId() . '"]');
        $this->assertCount(1, $deleteButton, 'Le bouton de suppression pour la salle n\'a pas été trouvé.');

        // Simuler l'envoi du formulaire de suppression
        $client->request('POST', '/charge/salles/supprimer/' . $room->getId());
        $this->assertResponseRedirects('/charge/salles');

        // Suivre la redirection
        $client->followRedirect();

        // Vérifier que la salle a été supprimée
        $deletedRoom = $roomRepository->find($room->getId());
        $this->assertNull($deletedRoom, 'La salle n\'a pas été supprimée de la base de données.');
    }
    /*
     * Test if deleting a non-existent room is handled gracefully
     */
    public function testDeleteNonExistentRoom()
    {
        $client = static::createClient();

        // create a id room 999
        $nonExistentId = 9999;

        // valid form
        $client->request('POST', '/charge/salles/supprimer/' . $nonExistentId);

        // check assert = 404
        $this->assertResponseStatusCodeSame(404);
    }
}