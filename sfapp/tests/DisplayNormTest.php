<?php

namespace App\Tests;

use App\Entity\Norm;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DisplayNormTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        // Create a client and a link with the database
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // Add summmer and winter norms in the test database
        $summerNorm = new Norm();
        $summerNorm->setSeason('summer')
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($summerNorm);

        $winterNorm = new Norm();
        $winterNorm->setSeason('winter')
            ->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(15)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(300)
            ->setCo2MaxNorm(900);
        $this->entityManager->persist($winterNorm);

        $this->entityManager->flush();
    }
    public function testAccessToPage(): void
    {
        $crawler = $this->client->request('GET', '/charge/salles/normes');

        $this->assertResponseIsSuccessful();
    }

    public function testIsSummerNorm(): void
    {
        // Envoie la requête GET pour accéder à la page
        $crawler = $this->client->request('GET', '/charge/salles/normes');

        // Vérifie que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Récupère les valeurs de la norme d'été depuis la page
        $humidityMaxText = $crawler->filter('#humidity-max')->text();
        $temperatureMaxText = $crawler->filter('#temperature-max')->text();
        $co2MaxText = $crawler->filter('#CO2-max')->text();

        $humidityMinText = $crawler->filter('#humidity-min')->text();
        $temperatureMinText = $crawler->filter('#temperature-min')->text();
        $co2MinText = $crawler->filter('#CO2-min')->text();

        // Récupère les normes d'été en base de données
        $summerNorm = $this->entityManager->getRepository(Norm::class)->findOneBy(['season' => 'summer']);

        // Vérifie que les valeurs des normes d'été sont affichées correctement
        $this->assertStringContainsString('Max : ' . $summerNorm->getHumidityMaxNorm(), $humidityMaxText);
        $this->assertStringContainsString('Min : ' . $summerNorm->getHumidityMinNorm(), $humidityMinText);

        $this->assertStringContainsString('Max : ' . $summerNorm->getTemperatureMaxNorm(), $temperatureMaxText);
        $this->assertStringContainsString('Min : ' . $summerNorm->getTemperatureMinNorm(), $temperatureMinText);

        $this->assertStringContainsString('Max : ' . $summerNorm->getCo2MaxNorm(), $co2MaxText);
        $this->assertStringContainsString('Min : ' . $summerNorm->getCo2MinNorm(), $co2MinText);
    }

    /*
     * Impossible to test winter norms because it's made with javascript
     *
    public function testIsWinterNorm(): void
    {
        $crawler = $this->client->request('GET', '/charge/salles/normes');

        $this->assertResponseIsSuccessful();

        $winterButton = $crawler->filter('#showWinter');
        $crawler = $winterButton->click();
        $crawler = $winterButton->

        $humidityMaxText = $crawler->filter('#humidity-max')->text();
        $temperatureMaxText = $crawler->filter('#temperature-max')->text();
        $co2MaxText = $crawler->filter('#CO2-max')->text();

        $humidityMinText = $crawler->filter('#humidity-min')->text();
        $temperatureMinText = $crawler->filter('#temperature-min')->text();
        $co2MinText = $crawler->filter('#CO2-min')->text();

        $winterNorm = $this->entityManager->getRepository(Norm::class)->findOneBy(['season' => 'winter']);

        $this->assertStringContainsString('Max : ' . $winterNorm->getHumidityMaxNorm(), $humidityMaxText);
        $this->assertStringContainsString('Min : ' . $winterNorm->getHumidityMinNorm(), $humidityMinText);

        $this->assertStringContainsString('Max : ' . $winterNorm->getTemperatureMaxNorm(), $temperatureMaxText);
        $this->assertStringContainsString('Min : ' . $winterNorm->getTemperatureMinNorm(), $temperatureMinText);

        $this->assertStringContainsString('Max : ' . $winterNorm->getCo2MaxNorm(), $co2MaxText);
        $this->assertStringContainsString('Min : ' . $winterNorm->getCo2MinNorm(), $co2MinText);

    }
    */
}
