<?php

namespace App\DataFixtures;

use App\Entity\Norm;
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
        // Norm summer
        $summerNorm = new Norm();
        $summerNorm->setSeason('summer')
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $manager->persist($summerNorm);

        // Norm winter
        $winterNorm = new Norm();
        $winterNorm->setSeason('winter')
            ->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(15)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(300)
            ->setCo2MaxNorm(900);
        $manager->persist($winterNorm);

        // Creation of the SA alone
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

        $room7 = new Room();
        $room7->setRoomName("D307");
        $room7->setNbWindows(4);
        $room7->setNbRadiator(2);
        $manager->persist($room7);

        $sa7 = new Sa();
        $sa7->setState(SAState::Available);
        $sa7->setCO2(650);
        $sa7->setHumidity(55);
        $sa7->setTemperature(20);
        $manager->persist($sa7);



        $sa8 = new Sa();
        $sa8->setState(SAState::Available);
        $sa8->setCO2(2000);
        $sa8->setHumidity(90);
        $sa8->setTemperature(31);
        $manager->persist($sa8);

        $manager->flush();
    }
}