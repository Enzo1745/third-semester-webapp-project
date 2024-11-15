<?php

namespace App\DataFixtures;

use App\Entity\Room;
use App\Entity\Sa;
use App\Repository\Model\SaState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sa1 = new Sa();
        $sa1->setState(SaState::Available);
        $manager->persist($sa1);

        $sa2 = new Sa();
        $sa2->setState(SaState::Available);
        $manager->persist($sa2);

        $room1 = new Room();
        $room1->setRoomName("D204");
        $manager->persist($room1);

        $sa3 = new Sa();
        $sa3->setState(SaState::Functional);
        $sa3->setRoom($room1);
        $manager->persist($sa3);

        $room2 = new Room();
        $room2->setRoomName("D205");
        $manager->persist($room2);

        $room3 = new Room();
        $room3->setRoomName("D206");
        $manager->persist($room3);

        $room4 = new Room();
        $room4->setRoomName("D304");
        $manager->persist($room4);

        $manager->flush();
    }
}
