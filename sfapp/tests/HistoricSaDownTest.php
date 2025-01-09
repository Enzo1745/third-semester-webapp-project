<?php

namespace App\Tests;

use App\Entity\Sa;
use App\Entity\Down;
use App\Repository\Model\SAState;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HistoricSaDownTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
        // Purge and seed database
        $this->createTestData();

    }

    private function createTestData(): void
    {
        // Create and persist test data
        $sa = new Sa();
        $sa->setId(1);
        $sa->setState(SAState::Down);
        $this->entityManager->persist($sa);

        $sa2 = new Sa();
        $sa2->setId(2);
        $sa2->setState(SAState::Available);
        $this->entityManager->persist($sa2);


        $down = new Down();
        $down->setSa($sa);
        $down->setDate(new \DateTime('now'));
        $down->setReason('Test reason');
        $down->setCO2(true);
        $down->setTemperature(true);
        $down->setHumidity(false);
        $down->setMicrocontroller(true);
        $this->entityManager->persist($down);

        $this->entityManager->flush();
    }


    public function testHistoricSaDown(): void
    {
        $crawler = $this->client->request('GET', '/technicien/sa/panne/historique');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.card-header', 'Date');
        $this->assertSelectorTextContains('.card-header', 'SA');
        $this->assertSelectorExists('.col-2', '1'); // Check SA ID
        $this->assertSelectorTextContains('.col-2', 'Date'); // Check date
    }

    public function testFiltersWithResults(): void
    {

        $this->client->request('GET', '/technicien/sa/panne/historique', [
            'down_history[filtrer]' => 1,
            'down_history[dateBeg]' => '2025-01-01',
            'down_history[dateEnd]' => '2025-01-10',
        ]);


        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.row', 'Filtered results are displayed.');
    }







}
