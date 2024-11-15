<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DisplayRoomsTest extends WebTestCase
{
    /**
     * Test case: Verifying the details of a salle associated with a SA that has data.
     * This test checks if the temperature, humidity, and CO2 values are correctly displayed on the page.
     */
    public function testSearchingDetailsOfAssociatedSAWithData(): void
    {
        $client = static::createClient();

        $sa = new Sa();
        $sa->setEtat(SAState::Fonctionnel);
        $sa->setTemperature(25);
        $sa->setHumidite(60);
        $sa->setCO2(1200);

        $salle = new Room();
        $salle->setNumSalle("D101");
        $salle->setIdSA($sa);

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($salle);
        $entityManager->persist($sa);
        $entityManager->flush();

        $crawler = $client->request('GET', '/charge/salles/liste/D101');

        $this->assertSelectorTextContains('.value.temp', '25°');

        $this->assertSelectorTextContains('.value.humidity', '60%');

        $this->assertSelectorTextContains('.value.co2', '1200');
    }

    /**
     * Test case: Verifying the details of a salle associated with a SA that has no data.
     * This test checks if the "Aucunes données" message is displayed when the SA has no temperature, humidity, or CO2 values.
     */
    public function testSearchingDetailsOfAssociatedSAWithNoData(): void
    {
        $client = static::createClient();

        $sa = new Sa();
        $sa->setEtat(SAState::Fonctionnel);

        $salle = new Room();
        $salle->setNumSalle("D301");
        $salle->setIdSA($sa);

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($salle);
        $entityManager->persist($sa);
        $entityManager->flush();

        $crawler = $client->request('GET', '/charge/salles/liste/D301');

        $this->assertSelectorTextContains('.salle-details', 'Aucunes données');
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

        $crawler = $client->request('GET', '/charge/salles/liste');

        $this->assertSelectorTextContains('.noData', 'Aucun SA associé');
    }
}
