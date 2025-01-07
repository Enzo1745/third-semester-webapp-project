<?php

namespace App\Tests;

use App\Repository\Model\SAState;
use App\Service\DiagnocticService;
use ContainerVUHUmf6\get_Maker_AutoCommand_MakeSerializerNormalizer_LazyService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use DateTime;

class DiagnosticSaisonTest extends WebTestCase
{
    private Norm $summerNorms;
    private Norm $winterNorms;

    protected function setUp(): void
    {
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

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusSummerYellow1(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(5); // Température trop basse pour l'été
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusSummerYellow2(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(27);
        $sa->setHumidity(75); // Humidité trop élevée pour l'été
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusSummerRed(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(35); // Température trop élevée pour l'été
        $sa->setHumidity(85);
        $sa->setCO2(2800); // CO2 trop élevé

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->summerNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('red', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusWinterGreen(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(15); // Température correcte pour l'hiver
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('green', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusWinterYellow1(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(-10); // Température trop basse pour l'hiver
        $sa->setHumidity(50);
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusWinterYellow2(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(23);
        $sa->setHumidity(85); // Humidité trop élevée pour l'hiver
        $sa->setCO2(800);

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('yellow', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
    }

    public function testDiagnosticStatusWinterRed(): void
    {
        $diagnosticService = new DiagnocticService();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(50); // Température trop basse pour l'hiver
        $sa->setHumidity(90); // Humidité trop élevée pour l'hiver
        $sa->setCO2(2100); // CO2 trop élevé

        $room = new Room();
        $room->setRoomName("Room1");

        $compliantCount = $this->calculateCompliantCount($this->winterNorms, $sa);

        $status = $diagnosticService->getDiagnosticStatus($sa, $room, $this->summerNorms, $this->winterNorms, $compliantCount);
        $this->assertEquals('red', $status);

        $backtrace = debug_backtrace();
        $testName = $backtrace[0]['function'];

        dump("Test function name: " . $testName);
        dump("Compliant Count: " . $compliantCount);
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
