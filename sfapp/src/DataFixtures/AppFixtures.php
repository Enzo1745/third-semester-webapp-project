<?php

namespace App\DataFixtures;

use App\Entity\Down;
use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\User;
use App\Entity\Norm;
use App\Repository\Model\SAState;
use App\Repository\Model\UserRoles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des normes (Summer, Winter)
        $summerNorm = new Norm();
        $summerNorm->setSeason('summer')
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $manager->persist($summerNorm);

        $winterNorm = new Norm();
        $winterNorm->setSeason('winter')
            ->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(15)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(300)
            ->setCo2MaxNorm(900);
        $manager->persist($winterNorm);

        // Création des systèmes d'acquisition (SA) avec différents statuts
        $sa1 = new Sa();
        $sa1->setState(SAState::Available)
            ->setCO2(4000)
            ->setHumidity(51)
            ->setTemperature(19);
        $manager->persist($sa1);

        $sa2 = new Sa();
        $sa2->setState(SAState::Functional)
            ->setCO2(4800)
            ->setHumidity(50)
            ->setTemperature(16);
        $manager->persist($sa2);

        $sa3 = new Sa();
        $sa3->setState(SAState::Down)
            ->setCO2(5000)
            ->setHumidity(47)
            ->setTemperature(19);
        $manager->persist($sa3);

        $sa4 = new Sa();
        $sa4->setState(SAState::Waiting)
            ->setCO2(4600)
            ->setHumidity(48)
            ->setTemperature(17);
        $manager->persist($sa4);

        // Création des salles et association en fonction des statuts
        $room1 = new Room();
        $room1->setRoomName("D204")
            ->setNbWindows(2)
            ->setNbRadiator(1)
            ->setSa($sa1);
        $manager->persist($room1);

        $room2 = new Room();
        $room2->setRoomName("D205")
            ->setNbWindows(2)
            ->setNbRadiator(1)
            ->setSa($sa2);
        $manager->persist($room2);

        $room3 = new Room();
        $room3->setRoomName("D206")
            ->setNbWindows(4)
            ->setNbRadiator(2)
            ->setSa($sa3);
        $manager->persist($room3);

        $room4 = new Room();
        $room4->setRoomName("D304")
            ->setNbWindows(5)
            ->setNbRadiator(3)
            ->setSa($sa4);
        $manager->persist($room4);

        $room5 = new Room();
        $room5->setRoomName("D306")
            ->setNbWindows(4)
            ->setNbRadiator(2);
        $manager->persist($room5);

        // Création des utilisateurs
        $user1 = new User();
        $user1->setUsername('chargé')
            ->setPassword('1234')
            ->setRole(UserRoles::Charge);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('tech')
            ->setPassword('5678')
            ->setRole(UserRoles::Technicien);
        $manager->persist($user2);

        $sa5 = new Sa();
        $sa5->setTemperature(20)
            ->setHumidity(40)
            ->setCO2(1000)
            ->setState(SAState::Down);
        $manager->persist($sa5);

        $room5 = new Room();
        $room5->setRoomName("D306")
            ->setNbWindows(4)
            ->setNbRadiator(2)
            ->setSa($sa5);
        $manager->persist($room5);

        $down1 = new Down();
        $down1->setSa($sa5)
            ->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')))
            ->setCO2(true)
            ->setTemperature(false)
            ->setHumidity(false)
            ->setMicrocontroller(false)
            ->setReason("Capteur de CO2 en panne.");
        $manager->persist($down1);

        $sa6 = new Sa();
        $sa6->setState(SAState::Functional);
        $manager->persist($sa6);

        $room6 = new Room();
        $room6->setRoomName("D305")
            ->setNbWindows(4)
            ->setNbRadiator(2)
            ->setSa($sa6);
        $manager->persist($room6);

        $manager->flush();
    }
}
