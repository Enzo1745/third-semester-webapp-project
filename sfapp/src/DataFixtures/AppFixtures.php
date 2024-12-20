<?php

namespace App\DataFixtures;

use App\Entity\Norm;
use App\Entity\Room;
use App\Entity\Sa;
use App\Entity\Tips;
use App\Entity\User;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use App\Repository\Model\SAState;
use App\Repository\Model\UserRoles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
	private function tipsFixtures(ObjectManager $manager) : void
	{

		$tips1 = new Tips();
		$tips1->setContent("Ouvre la porte quand il fait chaud");
		$manager->persist($tips1);

		$tips2 = new Tips();
		$tips2->setContent("Utiliser la lumiere naturelle plutôt que la lumiere artificielle");
		$manager->persist($tips2);

		$tips3 = new Tips();
		$tips3->setContent("Ouvrir la fenêtre quand il fait chaud");
		$manager->persist($tips3);

		$tips4 = new Tips();
		$tips4->setContent("N'ouvre pas la fenêtre si il y a le chauffage");
		$manager->persist($tips4);

		$tips5 = new Tips();
		$tips5->setContent("Éteignez vos objets electroniques lorsque vous ne vous en servez plus");
		$manager->persist($tips5);

	}
    private function normFixtures(ObjectManager $manager): void
    {
        $norm = new Norm();
        $norm->setHumidityMinNorm(30);
        $norm->setHumidityMaxNorm(70);
        $norm->setTemperatureMinNorm(10);
        $norm->setTemperatureMaxNorm(25);
        $norm->setCo2MinNorm(650);
        $norm->setCo2MaxNorm(1600);
        $norm->setNormSeason(NormSeason::Summer);
        $norm->setNormType(NormType::Comfort);
        $manager->persist($norm);

        $norm2 = new Norm();
        $norm2->setHumidityMinNorm(40);
        $norm2->setHumidityMaxNorm(80);
        $norm2->setTemperatureMinNorm(-5);
        $norm2->setTemperatureMaxNorm(20);
        $norm2->setCo2MinNorm(650);
        $norm2->setCo2MaxNorm(1600);
        $norm2->setNormSeason(NormSeason::Winter);
        $norm2->setNormType(NormType::Comfort);
        $manager->persist($norm2);

        $technicalNorm = new Norm();
        $technicalNorm->setHumidityMinNorm(30);
        $technicalNorm->setHumidityMaxNorm(70);
        $technicalNorm->setTemperatureMinNorm(-15);
        $technicalNorm->setTemperatureMaxNorm(45);
        $technicalNorm->setCo2MinNorm(500);
        $technicalNorm->setCo2MaxNorm(2000);
        $technicalNorm->setNormSeason(NormSeason::Summer);
        $technicalNorm->setNormType(NormType::Technical);
        $manager->persist($technicalNorm);

        $technicalNorm2 = new Norm();
        $technicalNorm2->setHumidityMinNorm(30);
        $technicalNorm2->setHumidityMaxNorm(80);
        $technicalNorm2->setTemperatureMinNorm(-15);
        $technicalNorm2->setTemperatureMaxNorm(45);
        $technicalNorm2->setCo2MinNorm(500);
        $technicalNorm2->setCo2MaxNorm(2000);
        $technicalNorm2->setNormSeason(NormSeason::Winter);
        $technicalNorm2->setNormType(NormType::Technical);
        $manager->persist($technicalNorm2);


    }

    private function usersFixtures(ObjectManager $manager): void
    {
        // Create Users
        $user1 = new User();
        $user1->setUsername('charge')->setPassword('1234')->setRole(UserRoles::Charge);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername('tech')->setPassword('5678')->setRole(UserRoles::Technicien);
        $manager->persist($user2);


    }

    private function roomsSAFixtures(ObjectManager $manager): void
    {
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

    }
    public function load(ObjectManager $manager): void
    {

		$this->tipsFixtures($manager);
        $this->normFixtures($manager);
        $this->usersFixtures($manager);
        $this->roomsSAFixtures($manager);

        $manager->flush();
    }
}
