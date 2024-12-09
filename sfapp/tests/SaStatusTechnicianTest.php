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

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Nettoyage de la base de données
        $this->entityManager->createQuery('DELETE FROM App\Entity\Sa')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();

        $statuses = [
            SAState::Functional,
            SAState::Down,
            SAState::Available,
            SAState::Waiting,
        ];

        foreach ($statuses as $index => $state) { // Pour chaque état, crée un SA
            $sa = new Sa();
            $sa->setState($state); // enum
            $sa->setTemperature(25 + $index);
            $sa->setHumidity(50 + $index);
            $sa->setCo2(1000 + $index * 100);

            $this->entityManager->persist($sa);
        }

        $this->entityManager->flush();
    }

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
