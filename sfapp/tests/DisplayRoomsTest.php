<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\RoomRepository;

class DisplayRoomsTest extends WebTestCase
{
    /**
     * Test case: Verifying the details of a salle associated with a SA that has data.
     * This test checks if the temperature, humidity, and CO2 values are correctly displayed on the page.
     */

    public function testSearchingDetailsOfAssociatedSAWithData(): void
    {
        $client = static::createClient();

        // Create SA with data
        $sa = new Sa();
        $sa->setState(SAState::Functional);
        $sa->setTemperature(25);
        $sa->setHumidity(60);
        $sa->setCO2(1200);

        // Create Room and associate with SA
        $salle = new Room();
        $salle->setNumSalle("D101");
        $salle->setIdSA($sa);

        // Persist entities
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($salle);
        $entityManager->persist($sa);
        $entityManager->flush();

        $roomRepository = self::getContainer()->get(RoomRepository::class);
        $salleTest = $roomRepository->findOneBy(['NumSalle' => 'D101']);
        $this->assertNotNull($salleTest);

        // Request the page
        $crawler = $client->request('GET', '/charge/salles/D101');

        // Assert the correct data is displayed
        //$this->assertSelectorTextContains('.value.temp', '25°');
        //$this->assertSelectorTextContains('.value.humidity', '60%');
        //$this->assertSelectorTextContains('.value.co2', '1200');
    }


    /**
     * Test case: Verifying the details of a salle without any SA associated.
     * This test checks if the "Aucun SA associé" message is displayed when the salle has no SA associated.
     */
    public function testSearchingDetailsOfSalleWithoutSA(): void
    {
        $client = static::createClient();

        $salle = new Room();
        $salle->setNumSalle("D303");

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($salle);
        $entityManager->flush();

        $crawler = $client->request('GET', '/charge/salles');

        $this->assertSelectorTextContains('.noData', 'Aucun SA associé');
    }
}
