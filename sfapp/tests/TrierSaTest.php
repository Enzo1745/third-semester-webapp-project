<?php

namespace App\Tests;

use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use App\Repository\Model\SAState;
use App\Service\DiagnocticService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrierSaTest extends WebTestCase
{
    private DiagnocticService $diagnosticService;

    protected function setUp(): void
    {
        // Initialize the service to be tested
        $this->diagnosticService = new DiagnocticService();
    }

    public function testSortRoomsByDiagnosticGood(): void
    {
        $norm = $this->createMockNorm();
        $rooms = $this->createTestRooms($norm);

        // (green > yellow > red > grey)
        usort($rooms, function ($a, $b) {
            $order = ['green' => 4, 'yellow' => 3, 'red' => 2, 'grey' => 1];
            return $order[$b['diagnosticStatus']] <=> $order[$a['diagnosticStatus']];
        });

        $sortedNames = array_column($rooms, 'name');

        // check order
        $expectedOrder = ['Room A', 'Room B', 'Room D', 'Room C', 'Room E'];
        $this->assertSame($expectedOrder, $sortedNames, 'The rooms are not sorted correctly for diagnostic "good".');
    }

    public function testSortRoomsByDiagnosticBad(): void
    {
        $norm = $this->createMockNorm();
        $rooms = $this->createTestRooms($norm);

        // (grey > red > yellow > green)
        usort($rooms, function ($a, $b) {
            $order = ['grey' => 4, 'red' => 3, 'yellow' => 2, 'green' => 1];
            return $order[$b['diagnosticStatus']] <=> $order[$a['diagnosticStatus']];
        });

        $sortedNames = array_column($rooms, 'name');

        //check order
        $expectedOrder = ['Room E', 'Room C', 'Room D', 'Room A', 'Room B'];
        $this->assertSame($expectedOrder, $sortedNames, 'The rooms are not sorted correctly for diagnostic "bad".');
    }

    public function testGetDiagnosticStatusWithNullSaOrRoom(): void
    {
        $norm = $this->createMockNorm();

        // Test with null Sa
        $result = $this->diagnosticService->getDiagnosticStatus(null, new Room(), $norm);
        $this->assertEquals('grey', $result, 'Diagnostic should return grey when SA is null.');

        // Test with null Room
        $result = $this->diagnosticService->getDiagnosticStatus(new Sa(), null, $norm);
        $this->assertEquals('grey', $result, 'Diagnostic should return grey when Room is null.');
    }

    public function testGetDiagnosticStatusWithSaInWaitingOrAvailableState(): void
    {
        $norm = $this->createMockNorm();
        $room = new Room();

        $sa = new Sa();
        $sa->setState(SAState::Waiting);

        // Test with SA in Waiting state
        $result = $this->diagnosticService->getDiagnosticStatus($sa, $room, $norm);
        $this->assertEquals('grey', $result, 'Diagnostic should return grey when SA is in Waiting state.');

        // Test with SA in Available state
        $sa->setState(SAState::Available);
        $result = $this->diagnosticService->getDiagnosticStatus($sa, $room, $norm);
        $this->assertEquals('grey', $result, 'Diagnostic should return grey when SA is in Available state.');
    }

    public function testGetDiagnosticStatusWithAllNormsCompliant(): void
    {
        $norm = $this->createMockNorm();
        $room = new Room();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(20);
        $sa->setHumidity(50);
        $sa->setCO2(600);

        // Test when all norms are compliant
        $result = $this->diagnosticService->getDiagnosticStatus($sa, $room, $norm);
        $this->assertEquals('green', $result, 'Diagnostic should return green when all norms are respected.');
    }

    public function testGetDiagnosticStatusWithNoNormsCompliant(): void
    {
        $norm = $this->createMockNorm();
        $room = new Room();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(10); // Below norm
        $sa->setHumidity(10);    // Below norm
        $sa->setCO2(2000);       // Above norm

        // Test when no norms are compliant
        $result = $this->diagnosticService->getDiagnosticStatus($sa, $room, $norm);
        $this->assertEquals('red', $result, 'Diagnostic should return red when no norms are respected.');
    }

    public function testGetDiagnosticStatusWithPartialNormsCompliant(): void
    {
        $norm = $this->createMockNorm();
        $room = new Room();

        $sa = new Sa();
        $sa->setState(SAState::Installed);
        $sa->setTemperature(20); // Compliant
        $sa->setHumidity(10);    // Not compliant
        $sa->setCO2(2000);       // Not compliant

        // Test when some norms are compliant
        $result = $this->diagnosticService->getDiagnosticStatus($sa, $room, $norm);
        $this->assertEquals('yellow', $result, 'Diagnostic should return yellow when some norms are respected.');
    }



    private function createTestRooms(Norm $norm): array
    {
        $diagnosticService = new DiagnocticService();

        $rooms = [
            [
                'name' => 'Room A',
                'sa' => $this->createSa(SAState::Installed, 20, 50, 600), // All norms respected (green)
            ],
            [
                'name' => 'Room B',
                'sa' => $this->createSa(SAState::Installed, 22, 55, 800), // All norms respected (green)
            ],
            [
                'name' => 'Room C',
                'sa' => $this->createSa(SAState::Installed, 15, 10, 1200), // No norms respected (red)
            ],
            [
                'name' => 'Room D',
                'sa' => $this->createSa(SAState::Installed, 18, 40, null), // Some norms respected (yellow)
            ],
            [
                'name' => 'Room E',
                'sa' => $this->createSa(SAState::Available, null, null, null), // SA not diagnosable (grey)
            ],
        ];

        // Ajout du diagnostic status
        foreach ($rooms as &$room) {
            $room['diagnosticStatus'] = $diagnosticService->getDiagnosticStatus($room['sa'], new Room(), $norm);
        }

        return $rooms;
    }

    private function createSa(SAState $state, ?int $temperature, ?int $humidity, ?int $co2): Sa
    {
        $sa = new Sa();
        $sa->setState($state);
        $sa->setTemperature($temperature ?? 0);
        $sa->setHumidity($humidity ?? 0);
        $sa->setCO2($co2 ?? 0);

        return $sa;
    }

    private function createMockNorm(): Norm
    {
        $norm = new Norm();
        $norm->setTemperatureMinNorm(18);
        $norm->setTemperatureMaxNorm(24);
        $norm->setHumidityMinNorm(30);
        $norm->setHumidityMaxNorm(60);
        $norm->setCo2MinNorm(400);
        $norm->setCo2MaxNorm(1000);

        return $norm;
    }
}
