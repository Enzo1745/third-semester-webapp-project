<?php

namespace App\Tests;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class SaStatusTechnicianTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private $client;

    /**
     * @brief Set up the test environment
     *
     * This method creates and persists sample SA entities with different states (Functional, Down, Available, Waiting)
     * to simulate the data for the tests.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Nettoyage de la base de données
        $this->entityManager->createQuery('DELETE FROM App\Entity\Down')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Sa')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();


        $sa = new Sa();
        $sa->setId(1);
        $sa->setState(SAState::Installed);
        $sa->setTemperature(25);
        $sa->setHumidity(50);
        $sa->setCo2(1000);

        $sa = new Sa();
        $sa->setId(2);
        $sa->setState(SAState::Down);
        $sa->setTemperature(25);
        $sa->setHumidity(50);
        $sa->setCo2(1000);

        $sa = new Sa();
        $sa->setId(3);
        $sa->setState(SAState::Available);
        $sa->setTemperature(25);
        $sa->setHumidity(50);
        $sa->setCo2(1000);

        $sa = new Sa();
        $sa->setId(4);
        $sa->setState(SAState::Waiting);
        $sa->setTemperature(25);
        $sa->setHumidity(50);
        $sa->setCo2(1000);
        $this->entityManager->persist($sa);


        $this->entityManager->flush();
    }

    /**
     * @brief Test if the system acquisition with status "Functional" is displayed correctly
     */
    public function testSaStatusFunctional()
    {
        $crawler = $this->client->request('GET', '/technicien/sa');

        $rows = $crawler->filter('table.table tbody tr');
        $this->assertGreaterThan(0, $rows->count(), 'Aucune ligne de SA trouvée dans la table.');

        foreach ($rows as $row) {
            $rowCrawler = $crawler->filter('table.table tbody tr');
            $stateText = $rowCrawler->filter('.state')->first()->text();
            $statusClass = $rowCrawler->filter('td > span.rounded-circle')->first()->attr('class');

            if ($stateText === 'Fonctionnel') {
                $this->assertStringContainsString('bg-success', $statusClass);
            }
        }
    }

    /**
     * @brief Test if the system acquisition with status "Down" is displayed correctly
     */
    public function testSaStatusDown()
    {
        $crawler = $this->client->request('GET', '/technicien/sa');

        $rows = $crawler->filter('table.table tbody tr');
        $this->assertGreaterThan(0, $rows->count(), 'Aucune ligne de SA trouvée dans la table.');

        foreach ($rows as $row) {
            $rowCrawler = $crawler->filter('table.table tbody tr');
            $stateText = $rowCrawler->filter('.state')->first()->text();
            $statusClass = $rowCrawler->filter('td > span.rounded-circle')->first()->attr('class');

            if ($stateText === 'En panne') {
                $this->assertStringContainsString('bg-danger', $statusClass);
            }
        }
    }

    /**
     * @brief Test if the system acquisition with status "Available" is displayed correctly
     */
    public function testSaStatusAvailable()
    {
        $crawler = $this->client->request('GET', '/technicien/sa');

        $rows = $crawler->filter('table.table tbody tr');
        $this->assertGreaterThan(0, $rows->count(), 'Aucune ligne de SA trouvée dans la table.');

        foreach ($rows as $row) {
            $rowCrawler = $crawler->filter('table.table tbody tr');
            $stateText = $rowCrawler->filter('.state')->first()->text();
            $statusClass = $rowCrawler->filter('td > span.rounded-circle')->first()->attr('class');

            if ($stateText === 'Disponible') {
                $this->assertStringContainsString('bg-secondary', $statusClass);
            }
        }
    }

    /**
     * @brief Test if the system acquisition with status "Waiting" is displayed correctly
     */
    public function testSaStatusWaiting()
    {
        $crawler = $this->client->request('GET', '/technicien/sa');

        $rows = $crawler->filter('table.table tbody tr');
        $this->assertGreaterThan(0, $rows->count(), 'Aucune ligne de SA trouvée dans la table.');

        foreach ($rows as $row) {
            $rowCrawler = $crawler->filter('table.table tbody tr');
            $stateText = $rowCrawler->filter('.state')->first()->text();
            $statusClass = $rowCrawler->filter('td > span.rounded-circle')->first()->attr('class');

            if ($stateText === 'En attente') {
                $this->assertStringContainsString('bg-secondary', $statusClass);
            }
        }
    }
}
