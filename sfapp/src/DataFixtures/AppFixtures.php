<?php

namespace App\DataFixtures;

use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\User;
use App\Repository\Model\SAState;
use App\Repository\Model\UserRoles;
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


        $sa3 = new Sa();
        $room3 = new Room();
        $room3->setRoomName("D206");
        $room3->setNbWindows(4);
        $room3->setNbRadiator(2);

        $room3->setSa($sa3);
        $sa3->setState(SAState::Functional);
        $manager->persist($sa3);
        $manager->persist($room3);

        $room4 = new Room();
        $room4->setRoomName("D304");
        $room4->setNbWindows(5);
        $room4->setNbRadiator(3);
        $manager->persist($room4);

        $user1 = new User();
        $user1->setUsername('chargé');
        $user1->setPassword('1234');
        $user1->setRole(UserRoles::Charge);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('tech');
        $user2->setPassword('5678');
        $user2->setRole(UserRoles::Technicien);
        $manager->persist($user2);


        $sa5 = new Sa();
        $room5 = new Room();
        $room5->setRoomName("D306");
        $room5->setNbWindows(4);
        $room5->setNbRadiator(2);

        $room5->setSa($sa3);
        $sa5->setState(SAState::Functional);
        $manager->persist($sa5);
        $manager->persist($room5);

        $sa6 = new Sa();
        $room6 = new Room();
        $room6->setRoomName("D305");
        $room6->setNbWindows(4);
        $room6->setNbRadiator(2);

        $room6->setSa($sa6);
        $sa6->setState(SAState::Functional);
        $manager->persist($sa6);
        $manager->persist($room6);



        $manager->flush();
    }
}