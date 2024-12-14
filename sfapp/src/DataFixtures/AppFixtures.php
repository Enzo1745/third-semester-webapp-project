<?php

namespace App\DataFixtures;

use App\Entity\Down;
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
        // Create Norms
        foreach (["Winter", "Spring", "Summer", "Fall"] as $season) {
            $norm = new Norm();
            $norm->setHumidityMinNorm(30);
            $norm->setHumidityMaxNorm(70);
            $norm->setTemperatureMinNorm(10);
            $norm->setTemperatureMaxNorm(25);
            $norm->setCo2MinNorm(400);
            $norm->setCo2MaxNorm(600);
            $norm->setSeason($season);

            $manager->persist($norm);
            $norms[] = $norm;
        }

        // Create Users
        $user1 = new User();
        $user1->setUsername('charge')->setPassword(password_hash('1234', PASSWORD_BCRYPT))->setRole(UserRoles::Charge);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('tech')->setPassword(password_hash('5678', PASSWORD_BCRYPT))->setRole(UserRoles::Technicien);
        $manager->persist($user2);




        // Create Room entities without SA associations


        $room1 = new Room();
        $room1->setRoomName("D205")->setNbWindows(2)->setNbRadiator(1);
        $manager->persist($room1);


        $room2 = new Room();
        $room2->setRoomName("D206")->setNbWindows(4)->setNbRadiator(2);
        $manager->persist($room2);

        $room3 = new Room();
        $room3->setRoomName("D304")->setNbWindows(5)->setNbRadiator(3);
        $manager->persist($room3);


        $room4 = new Room();
        $room4->setRoomName("D306")->setNbWindows(4)->setNbRadiator(2);
        $manager->persist($room4);


        $room5 = new Room();
        $room5->setRoomName("D307")->setNbWindows(4)->setNbRadiator(2);
        $manager->persist($room5);


        // Create SA entities

        $sa1 = new Sa();
        $sa1->setId(1);
        $sa1->setTemperature(22)->setHumidity(50)->setCO2(800)
            ->setState(SAState::Waiting)
            ->setRoom($room2);
        $room2->setSa($sa1);
        $manager->persist($sa1);


        $sa2 = new Sa();
        $sa2->setId(2);
        $sa2->setTemperature(25)->setHumidity(45)->setCO2(1200)
            ->setState(SAState::Available);
        $manager->persist($sa2);


        $sa3 = new Sa();
        $sa3->setId(3);
        $sa3->setState(SAState::Available);
        $manager->persist($sa3);


        $sa4 = new Sa();
        $sa4->setId(4);
        $sa4->setState(SAState::Available);
        $sa4->setTemperature(20)->setHumidity(40)->setCO2(1000);
        $manager->persist($sa4);


        $sa5 = new Sa();
        $sa5->setId(5);
        $sa5->setState(SAState::Installed);
        $sa5->setRoom($room1);
        $room1->setSa($sa5);
        $manager->persist($sa5);





        $manager->flush();
    }
}
