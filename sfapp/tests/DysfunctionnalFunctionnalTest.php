<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sa;
use App\Entity\Down;
use App\Repository\Model\SAState;
use \Doctrine\Common\DataFixtures\Purger\ORMPurger;

class DysfunctionnalFunctionnalTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Purger la base de données avant chaque test
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    private function createFunctionalSa(): Sa
    {
        $sa = new Sa();
        $sa->setState(SAState::Functional);
        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        return $sa;
    }

    private function createDown($sa, $reason, $temperature = false, $humidity = false, $co2 = false, $microcontroller = false)
    {

        $down = new Down();
        $down->setSa($sa)
        ->setReason($reason)
        ->setTemperature($temperature)
        ->setHumidity($humidity)
        ->setCo2($co2)
        ->setMicrocontroller($microcontroller)
        ->setDate(new \DateTime());


        $sa->setState(SAState::Down);


        $this->entityManager->persist($down);
        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        return $down;
    }


    public function testPageLoad(): void
    {
        $this->client->request('GET', '/technicien/sa/panne');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5.card-title', 'Déclarer un SA dysfonctionnel');
    }

    public function testInsertDown(): void
    {
        $sa = $this->createFunctionalSa();

        $crawler = $this->client->request('GET', '/technicien/sa/panne');
        $this->assertGreaterThan(0, $crawler->filter('form')->count(), 'Le formulaire n\'existe pas.');

        $form = $crawler->selectButton('Déclarer dysfonctionnel')->form([
            'sa_down[sa]' => $sa->getId(),
            'sa_down[reason]' => 'Température critique',
            'sa_down[temperature]' => true,
        ]);

        $this->client->submit($form);

        // Puisque le code redirige vers '#', nous ajustons le test en conséquence
        $this->assertResponseRedirects('#');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('tbody', 'Température critique');

        // Vérification que le SA est maintenant en état Down
        $updatedSa = $this->entityManager->getRepository(Sa::class)->find($sa->getId());
        $this->assertEquals(SAState::Down, $updatedSa->getState(), 'Le SA n\'a pas été mis à jour comme dysfonctionnel.');
    }

    public function testShowDown(): void
    {
        $sa = $this->createFunctionalSa();
        $this->createDown($sa, 'Température élevée', true);

        $crawler = $this->client->request('GET', '/technicien/sa/panne');
        $this->assertSelectorTextContains('tbody', 'Température élevée');
    }

    public function testRehabilitateSa(): void
    {
        $sa = $this->createFunctionalSa();
        $down = $this->createDown($sa, 'Température élevée', true);

        $this->client->request('POST', '/technicien/sa/panne/' . $down->getSa()->getId());
        $this->assertResponseRedirects('/technicien/sa/panne');

        $this->client->followRedirect();
        $this->assertSelectorTextNotContains('tbody', 'Température élevée');

        // Vérification que le SA est maintenant en état Functional
        $updatedSa = $this->entityManager->getRepository(Sa::class)->find($sa->getId());
        $this->assertEquals(SAState::Functional, $updatedSa->getState(), 'Le SA n\'a pas été réhabilité.');
    }

    public function testLastDownSa(): void
    {
        $sa = $this->createFunctionalSa();
        $this->createDown($sa, 'Température élevée', true);

        $crawler = $this->client->request('GET', '/technicien/sa/panne');
        $this->assertSelectorTextContains('tbody', 'Température élevée');
    }

    public function testInsertDownRemovesFromFunctional(): void
    {
        $sa = $this->createFunctionalSa();

        $crawler = $this->client->request('GET', '/technicien/sa/panne');
        $form = $crawler->selectButton('Déclarer dysfonctionnel')->form([
            'sa_down[sa]' => $sa->getId(),
            'sa_down[reason]' => 'Humidité excessive',
            'sa_down[humidity]' => true,
        ]);

        $this->client->submit($form);

        // Puisque le code redirige vers '#', nous ajustons le test en conséquence
        $this->assertResponseRedirects('#');

        $this->client->followRedirect();
        $this->assertSelectorTextContains('tbody', 'Humidité excessive');

        // Vérification que le SA n'est plus dans la liste des SA fonctionnels
        $saRepository = $this->entityManager->getRepository(Sa::class);
        $functionalSa = $saRepository->findBy(['state' => SAState::Functional]);
        $this->assertNotContains($sa, $functionalSa, 'Le SA est toujours marqué comme fonctionnel.');
    }
}
