<?php

namespace App\DataFixtures;

use App\Entity\Room;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //room whithout an aquisition system
        $room = new Room();
        $room->setRoomNumber('SalleTest');
        $manager->persist($room);
        $manager->flush();


        //room whith an aquisition system
        $room2 = new Room();
        $room2->setRoomNumber('SalleTestAvecSa');
        $room2->setIdAS(1);
        $manager->persist($room2);
        $manager->flush();

    }
}
