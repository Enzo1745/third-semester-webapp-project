<?php

namespace App\Service;

use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\Norm;
use App\Repository\Model\SAState;
use DateTime;

class DiagnocticService
{
    public function getDiagnosticStatus(?Sa $sa,?Room $room, Norm $summerNorms, Norm $winterNorms): string
    {
        //check status
        if (!$sa || !$room) {
            return 'grey';
        }

        if (
            $sa->getState() === SAState::Waiting ||
            $sa->getState() === SAState::Available
        )
        {
            return 'grey';
        }

        $currentDate = new DateTime();
        $currentYear = $currentDate->format('Y');

        $springStart = new DateTime("$currentYear-03-20");
        $summerEnd = new DateTime("$currentYear-09-22");

        $currentNorms = $currentDate >= $springStart && $currentDate <= $summerEnd
            ? $summerNorms
            : $winterNorms;

        $tempOk = $sa->getTemperature() >= $currentNorms->getTemperatureMinNorm()
            && $sa->getTemperature() <= $currentNorms->getTemperatureMaxNorm()
            && $sa->getTemperature() !== null;

        $humOk = $sa->getHumidity() >= $currentNorms->getHumidityMinNorm()
            && $sa->getHumidity() <= $currentNorms->getHumidityMaxNorm()
            && $sa->getHumidity() !== null;

        $co2Ok = $sa->getCO2() >= $currentNorms->getCo2MinNorm()
            && $sa->getCO2() <= $currentNorms->getCo2MaxNorm()
            && $sa->getCO2() !== null;

        // number of conformity
        $compliantCount = ($tempOk ? 1 : 0)
            + ($humOk  ? 1 : 0)
            + ($co2Ok  ? 1 : 0);

        // return number of conformity
        return match ($compliantCount) {
            3       => 'green',
            0       => 'red',
            default => 'yellow',
        };


    }
}
