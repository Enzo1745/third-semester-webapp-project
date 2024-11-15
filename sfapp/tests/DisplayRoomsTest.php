<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Salle;
use App\Repository\Model\EtatSA;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DisplayRoomsTest extends WebTestCase
{
    public function testSearchingDetailsOfAssociatedSAWithData(): void
    {
        $client = static::createClient();

        $sa = new Sa();
        $sa->setEtat(EtatSA::Fonctionnel);
        $sa->setTemperature(25);
        $sa->setHumidite(60);
        $sa->setCO2(1200);

        $salle = new Salle();
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

    public function testSearchingDetailsOfAssociatedSAWithNoData(): void
    {
        $client = static::createClient();

        $sa = new Sa();
        $sa->setEtat(EtatSA::Fonctionnel);

        $salle = new Salle();
        $salle->setNumSalle("D301");
        $salle->setIdSA($sa);

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($salle);
        $entityManager->persist($sa);
        $entityManager->flush();

        $crawler = $client->request('GET', '/charge/salles/liste/D301');

        $this->assertSelectorTextContains('.salle-details', 'Aucunes données');
    }

    public function testSearchingDetailsOfSalleWithoutSA(): void
    {
        $client = static::createClient();

        $salle = new Salle();
        $salle->setNumSalle("D303");

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($salle);
        $entityManager->flush();

        $crawler = $client->request('GET', '/charge/salles/liste');

        $this->assertSelectorTextContains('.noData', 'Aucun SA associé');
    }

}
