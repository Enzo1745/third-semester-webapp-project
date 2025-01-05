<?php

namespace App\Service;

use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\Norm;
use App\Repository\Model\SAState;

class DiagnocticService
{
    public function getDiagnosticStatus(?Sa $sa,?Room $room, Norm $summerNorms): string
    {
        //check status
        if (!$sa || !$room) {
            // Par exemple, on renvoie 'grey'
            return 'grey';
        }

        if (
            $sa->getState() === SAState::Waiting ||
            $sa->getState() === SAState::Available
        )
        {
            return 'grey';
        }

        // check conformity
        $tempOk = $sa->getTemperature() >= $summerNorms->getTemperatureMinNorm()
            && $sa->getTemperature() <= $summerNorms->getTemperatureMaxNorm() && $sa->getTemperature() != null;

        $humOk = $sa->getHumidity() >= $summerNorms->getHumidityMinNorm()
            && $sa->getHumidity() <= $summerNorms->getHumidityMaxNorm() && $sa->getHumidity() != null;

        $co2Ok = $sa->getCO2() >= $summerNorms->getCo2MinNorm()
            && $sa->getCO2() <= $summerNorms->getCo2MaxNorm() && $sa->getCO2() != null;

        // number of conformity
        $compliantCount = 0
            + ($tempOk ? 1 : 0)
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
