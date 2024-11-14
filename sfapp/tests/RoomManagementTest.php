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

        // going to add room page
        $crawler = $client->request('GET', '/charge/salles/ajouter');

        // valid form
        $form = $crawler->selectButton('Add')->form([
            'add_room[roomNumber]' => '201',
        ]);

        $client->submit($form);

        // check redirection after added a room
        $this->assertResponseRedirects('/charge/salles/ajouter');
        $client->followRedirect();

        // check that room added to bd
        $roomRepository = static::getContainer()->get(RoomRepository::class);
        $room = $roomRepository->findOneBy(['roomNumber' => '201']);

        $this->assertNotNull($room);

        // clear database
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->remove($room);
        $entityManager->flush();
    }

    /*
     * Test if a room can be successfully deleted
     */
    public function testDeleteRoom()
    {
        $client = static::createClient();

        // add room to test
        $room = new Room();
        $room->setRoomNumber('202');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($room);
        $entityManager->flush();

        // going to list room
        $crawler = $client->request('GET', '/charge/salles/liste');

        // finf form to delete room 202
        $deleteForm = $crawler->filter('form[action="/charge/salles/supprimer/' . $room->getId() . '"]');

        $this->assertCount(1, $deleteForm);

        // valid form
        $form = $deleteForm->form();
        $client->submit($form);

        // check redirection form
        $this->assertResponseRedirects('/charge/salles/liste');
        $client->followRedirect();

        // check room delete from database
        $roomRepository = static::getContainer()->get(RoomRepository::class);
        $deletedRoom = $roomRepository->find($room->getId());

        $this->assertNull($deletedRoom);
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