<?php

namespace App\Tests;

use App\Entity\Norm;
use App\Entity\Sa;
use App\Entity\Room;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use App\Repository\Model\SAState;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class DisplayRoomsTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $summerNorm = new Norm();
        $summerNorm->setNormSeason(NormSeason::Summer)
            ->setNormType(NormType::Comfort)
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($summerNorm);

        $winterNorm = new Norm();
        $winterNorm->setNormSeason(NormSeason::Winter)
            ->setNormType(NormType::Comfort)
            ->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(15)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(300)
            ->setCo2MaxNorm(900);
        $this->entityManager->persist($winterNorm);
    }

    public function testSearchingDetailsOfAssociatedSAWithData(): void
    {
        // Create et persist the Room
        $room = new Room();
        $room->setRoomName("D101");
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);

        $this->entityManager->flush();

        // Créer le SA et l'associer à la Room
        $sa = new Sa();
        $sa->setId(1);
        $sa->setState(SAState::Installed);
        $sa->setTemperature(25);
        $sa->setHumidity(60);
        $sa->setCO2(1200);
        $sa->setRoom($room); // Établir la relation
        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        // Requête de la page
        $crawler = $this->client->request('GET', '/charge/salles/D101');

        $this->assertResponseIsSuccessful();

        // Vérifier le titre de la page
        $this->assertSelectorTextContains('h1.h2.fw-bold', 'Détails de la salle D101');


        //$this->assertSelectorTextContains('.detail-block .value.temp', '25°');
        // $this->assertSelectorTextContains('.detail-block .value.humidity', '60%');
        //$this->assertSelectorTextContains('.detail-block .value.co2', '1200');
    }

    public function testSearchingDetailsOfSalleWithoutSA(): void
    {
        // Créez la Room sans SA
        $room = new Room();
        $room->setRoomName("D303");
        $room->setNbRadiator(2);
        $room->setNbWindows(4);
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        // Effectuez la requête
        $crawler = $this->client->request('GET', '/charge/salles/D303');

        $this->assertResponseIsSuccessful();


        //$this->assertSelectorTextContains('.salle-num', 'D303');
        //$this->assertSelectorTextContains('.salle-details', 'Aucune donnée');
    }
}