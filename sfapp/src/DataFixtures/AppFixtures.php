<?php

namespace App\DataFixtures;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SAState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des SAs sans salle associée
        $sa1 = new Sa();
        $sa1->setState(SAState::Available);
        $sa1->setCO2(4000);
        $sa1->setHumidity(51);
        $sa1->setTemperature(19);
        $manager->persist($sa1);

        $sa2 = new Sa();
        $sa2->setState(SAState::Available);
        $sa2->setCO2(4800);
        $sa2->setHumidity(50);
        $sa2->setTemperature(16);
        $manager->persist($sa2);

        // Création des salles
        $room1 = new Room();
        $room1->setRoomName("D204");
        $room1->setNbWindows(2);
        $room1->setNbRadiator(1);
        $manager->persist($room1);

        $sa3 = new Sa();
        $sa3->setState(SAState::Available);
        $manager->persist($sa3);

        $room2 = new Room();
        $room2->setRoomName("D205");
        $room2->setNbWindows(2);
        $room2->setNbRadiator(1);
        $manager->persist($room2);

        $room3 = new Room();
        $room3->setRoomName("D206");
        $room3->setNbWindows(4);
        $room3->setNbRadiator(2);
        $manager->persist($room3);

        $room4 = new Room();
        $room4->setRoomName("D304");
        $room4->setNbWindows(5);
        $room4->setNbRadiator(3);
        $manager->persist($room4);

        $manager->flush();
    }
}