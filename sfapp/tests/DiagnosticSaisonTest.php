<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\Model\SAState;
use App\Service\DiagnocticService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use DateTime;
use function PHPUnit\Framework\assertArrayHasKey;
use App\Controller\RoomController;
class DiagnosticSaisonTest extends WebTestCase
{
    private Norm $summerNorms;
    private Norm $winterNorms;

    private $client;
    private $entityManager;
    protected function setUp(): void
    {

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'charge_user']);
        if (!$existingUser) {
            $userCharge = new User();
            $userCharge->setUsername('charge_user')
                ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
                ->setRoles(['ROLE_CHARGE']);
            $this->entityManager->persist($userCharge);
            $this->entityManager->flush();
        }
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $this->summerNorms = new Norm();
        $this->summerNorms->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(10)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(650)
            ->setCo2MaxNorm(1600)
            ->setNormSeason(NormSeason::Summer)
            ->setNormType(NormType::Comfort);

        $this->winterNorms = new Norm();
        $this->winterNorms->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(-5)
            ->setTemperatureMaxNorm(20)
            ->setCo2MinNorm(650)
            ->setCo2MaxNorm(1600)
            ->setNormSeason(NormSeason::Winter)
            ->setNormType(NormType::Comfort);


    }


    /**
     * @uses externally calculate the compliant count to be able to change the season type
     * @param Norm $seasonNorm
     * @param Sa $sa
     * @return int
     */
    private function calculateCompliantCount(Norm $seasonNorm, Sa $sa): int
    {
        $tempOk = $sa->getTemperature() >= $seasonNorm->getTemperatureMinNorm()
            && $sa->getTemperature() <= $seasonNorm->getTemperatureMaxNorm();

        $humOk = $sa->getHumidity() >= $seasonNorm->getHumidityMinNorm()
            && $sa->getHumidity() <= $seasonNorm->getHumidityMaxNorm();

        $co2Ok = $sa->getCO2() >= $seasonNorm->getCo2MinNorm()
            && $sa->getCO2() <= $seasonNorm->getCo2MaxNorm();

        $compliantCount = ($tempOk ? 1 : 0)
            + ($humOk ? 1 : 0)
            + ($co2Ok ? 1 : 0);

        return $compliantCount;
    }

    public function testDiagnosticStatusSummerGreen(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(23);
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('green', $status);
    }

    public function testDiagnosticStatusSummerYellow1(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(5);
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);
    }

    public function testDiagnosticStatusSummerYellow2(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(27);
        $sa->setHumidity(75);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);
    }

    public function testDiagnosticStatusSummerRed(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(35);
        $sa->setHumidity(85);
        $sa->setCO2(2800); // CO2 trop élevé

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('red', $status);
    }

    public function testDiagnosticStatusWinterGreen(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(15);
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('green', $status);
    }

    public function testDiagnosticStatusWinterYellow1(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(-10);
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);
    }

    public function testDiagnosticStatusWinterYellow2(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(23);
        $sa->setHumidity(85);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);
    }

    public function testDiagnosticStatusWinterRed(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(50);
        $sa->setHumidity(90);
        $sa->setCO2(2100);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('red', $status);
    }

    public function testAffichageWinterText()
    {
        $diagnosticService = new DiagnocticService();
        $this->client->request('POST', '/connexion', [
            'username' => 'charge_user',
            'password' => 'password123',
        ]);
        $this->assertResponseRedirects('/charge/salles');
        $this->client->followRedirect();

        // Requête vers la route sécurisée
        $crawler = $this->client->request('GET', '/charge/salles');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $date = new \DateTime('2025-01-15');
        $season = $diagnosticService->getSeason($date);
        $this->assertEquals('Hiver', $season);

        // Vérifiez l'affichage correct sur la page
        $this->assertSelectorTextContains('#summerText', 'Saison de normes actuelle : Hiver');

    }


    public function testAffichageSummerText()
    {
        $diagnosticService = new DiagnocticService();
        $this->client->request('POST', '/connexion', [
            'username' => 'charge_user',
            'password' => 'password123',
        ]);
        $this->assertResponseRedirects('/charge/salles');
        $this->client->followRedirect();

        // Requête vers la route sécurisée
        $crawler = $this->client->request('GET', '/charge/salles');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $date = new \DateTime('2025-08-21');
        $season = $diagnosticService->getSeason($date);
        $this->assertEquals('Été', $season);





    }






    private function createNorm(NormSeason $season, NormType $type): Norm
    {
        $norm = new Norm();
        $norm->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(10)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(650)
            ->setCo2MaxNorm(1600)
            ->setNormSeason($season)
            ->setNormType($type);

        return $norm;
    }
}
