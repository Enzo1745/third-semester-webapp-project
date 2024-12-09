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
        // SA1 - Available
        $sa1 = new Sa();
        $sa1->setState(SAState::Available)
            ->setCO2(4000)
            ->setHumidity(51)
            ->setTemperature(19);
        $manager->persist($sa1);

        // SA2 - Functional
        $sa2 = new Sa();
        $sa2->setState(SAState::Functional)
            ->setCO2(4800)
            ->setHumidity(50)
            ->setTemperature(16);
        $manager->persist($sa2);

        // SA3 - Down (En panne)
        $sa3 = new Sa();
        $sa3->setState(SAState::Down)
            ->setCO2(5000)
            ->setHumidity(47)
            ->setTemperature(19);
        $manager->persist($sa3);

        // SA4 - Waiting (En attente)
        $sa4 = new Sa();
        $sa4->setState(SAState::Waiting)
            ->setCO2(4600)
            ->setHumidity(48)
            ->setTemperature(17);
        $manager->persist($sa4);

        // Création des salles et association en fonction des statuts
        // Salle D204 - associée à SA1 (Disponible)
        $room1 = new Room();
        $room1->setRoomName("D204")
            ->setNbWindows(2)
            ->setNbRadiator(1);
        $room1->setSa($sa1);  // Association avec SA1 (Disponible)
        $manager->persist($room1);

        // Salle D205 - associée à SA2 (Fonctionnel)
        $room2 = new Room();
        $room2->setRoomName("D205")
            ->setNbWindows(2)
            ->setNbRadiator(1);
        $room2->setSa($sa2);  // Association avec SA2 (Fonctionnel)
        $manager->persist($room2);

        // Salle D206 - associée à SA3 (En panne) - ne doit pas être associée à une salle fonctionnelle
        $room3 = new Room();
        $room3->setRoomName("D206")
            ->setNbWindows(4)
            ->setNbRadiator(2);
        $room3->setSa($sa3);  // Association avec SA3 (En panne), mais cette salle ne doit pas être utilisée
        $manager->persist($room3);

        // Salle D304 - associée à SA4 (En attente) - ne doit pas être associée à une salle fonctionnelle
        $room4 = new Room();
        $room4->setRoomName("D304")
            ->setNbWindows(5)
            ->setNbRadiator(3);
        $room4->setSa($sa4);  // Association avec SA4 (En attente)
        $manager->persist($room4);

        // Salle D306 - sans SA (En attente d'association)
        $room5 = new Room();
        $room5->setRoomName("D306")
            ->setNbWindows(4)
            ->setNbRadiator(2);
        $manager->persist($room5);

        // Création des utilisateurs
        $user1 = new User();
        $user1->setUsername('chargé')
            ->setPassword('1234')
            ->setRole(UserRoles::Charge); // Role chargé
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('tech')
            ->setPassword('5678')
            ->setRole(UserRoles::Technicien); // Role technicien
        $manager->persist($user2);

        // Sauvegarde de tout dans la base de données
        $manager->flush();
    }
}
