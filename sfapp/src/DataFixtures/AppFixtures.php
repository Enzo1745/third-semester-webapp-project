<?php

namespace App\DataFixtures;

use App\Entity\Down;
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
        $user1->setUsername($_ENV['CHARGE_USERNAME'])
            ->setPassword(password_hash($_ENV['CHARGE_PASSWORD'], PASSWORD_BCRYPT))
            ->setRoles(['ROLE_CHARGE']);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername($_ENV['TECHNICIEN_USERNAME'])
            ->setPassword(password_hash($_ENV['TECHNICIEN_PASSWORD'], PASSWORD_BCRYPT))
            ->setRoles(['ROLE_TECH']);
        $manager->persist($user2);


    }

    private function roomsSAFixtures(ObjectManager $manager): void
    {
        // Create Room entities without SA associations
        $room1 = new Room();
        $room1->setRoomName("D205")->setNbWindows(4)->setNbRadiator(2);

        $room2 = new Room();
        $room2->setRoomName("D206")->setNbWindows(4)->setNbRadiator(1);

        $room3 = new Room();
        $room3->setRoomName("D207")->setNbWindows(4)->setNbRadiator(2);

        $room4 = new Room();
        $room4->setRoomName("D204")->setNbWindows(6)->setNbRadiator(3);

        $room5 = new Room();
        $room5->setRoomName("D203")->setNbWindows(4)->setNbRadiator(2);

        $room6 = new Room();
        $room6->setRoomName("D303")->setNbWindows(4)->setNbRadiator(3);

        $room7 = new Room();
        $room7->setRoomName("D304")->setNbWindows(4)->setNbRadiator(2);

        $room8 = new Room();
        $room8->setRoomName("C101")->setNbWindows(4)->setNbRadiator(2);

        $room9 = new Room();
        $room9->setRoomName("D109")->setNbWindows(2)->setNbRadiator(1);

        $room10 = new Room();
        $room10->setRoomName("Secrétariat")->setNbWindows(2)->setNbRadiator(1);

        $room11 = new Room();
        $room11->setRoomName("D001")->setNbWindows(4)->setNbRadiator(3);

        $room12 = new Room();
        $room12->setRoomName("D002")->setNbWindows(8)->setNbRadiator(6);

        $room13 = new Room();
        $room13->setRoomName("D004")->setNbWindows(4)->setNbRadiator(3);

        $room14 = new Room();
        $room14->setRoomName("C004")->setNbWindows(6)->setNbRadiator(4);

        $room15 = new Room();
        $room15->setRoomName("C007")->setNbWindows(6)->setNbRadiator(2);

        // Create SA entities

        $sa1 = new Sa();
        $sa1->setName("ESP-004");
        $sa1->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room1);
        $room1->setIdSa($sa1->getId());
        $manager->persist($sa1);
        $manager->persist($room1);


        $sa2 = new Sa();
        $sa2->setName("ESP-008");
        $sa2->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Down)
            ->setRoom($room2);
        $room2->setIdSa($sa2->getId());
        $manager->persist($sa2);
        $manager->persist($room2);

        $sa3 = new Sa();
        $sa3->setName("ESP-006");
        $sa3->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room3);
        $room3->setIdSa($sa3->getId());
        $manager->persist($sa3);
        $manager->persist($room3);

        $sa4 = new Sa();
        $sa4->setName("ESP-014");
        $sa4->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room4);
        $room4->setIdSa($sa4->getId());
        $manager->persist($sa4);
        $manager->persist($room4);

        $sa5 = new Sa();
        $sa5->setName("ESP-012");
        $sa5->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room5);
        $room5->setIdSa($sa5->getId());
        $manager->persist($sa5);
        $manager->persist($room5);

        $sa6 = new Sa();
        $sa6->setName("ESP-005");
        $sa6->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room6);
        $room6->setIdSa($sa6->getId());
        $manager->persist($sa6);
        $manager->persist($room6);

        $sa7 = new Sa();
        $sa7->setName("ESP-011");
        $sa7->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room7);
        $room7->setIdSa($sa7->getId());
        $manager->persist($sa7);
        $manager->persist($room7);

        $sa8 = new Sa();
        $sa8->setName("ESP-007");
        $sa8->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room8);
        $room8->setIdSa($sa8->getId());
        $manager->persist($sa8);
        $manager->persist($room8);

        $sa9 = new Sa();
        $sa9->setName("ESP-024");
        $sa9->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Down) /////////////////////////////////////////////////////////////////////////:
            ->setRoom($room9);
        $room9->setIdSa($sa9->getId());
        $manager->persist($sa9);
        $manager->persist($room9);

        $sa10= new Sa();
        $sa10->setName("ESP-026");
        $sa10->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room10);
        $room10->setIdSa($sa10->getId());
        $manager->persist($sa10);
        $manager->persist($room10);

        $sa11 = new Sa();
        $sa11->setName("ESP-030");
        $sa11->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room11);
        $room11->setIdSa($sa11->getId());
        $manager->persist($sa11);
        $manager->persist($room11);

        $sa12 = new Sa();
        $sa12->setName("ESP-028");
        $sa12->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room12);
        $room12->setIdSa($sa12->getId());
        $manager->persist($sa12);
        $manager->persist($room12);

        $sa13 = new Sa();
        $sa13->setName("ESP-020");
        $sa13->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room13);
        $room13->setIdSa($sa13->getId());
        $manager->persist($sa13);
        $manager->persist($room13);

        $sa14 = new Sa();
        $sa14->setName("ESP-021");
        $sa14->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room14);
        $room14->setIdSa($sa14->getId());
        $manager->persist($sa14);
        $manager->persist($room14);

        $sa15 = new Sa();
        $sa15->setName("ESP-022");
        $sa15->setTemperature(null)->setHumidity(null)->setCO2(null)
            ->setState(SAState::Installed)
            ->setRoom($room15);
        $room15->setIdSa($sa15->getId());
        $manager->persist($sa15);
        $manager->persist($room15);

        //Temporary down fixture
        // SA 1
        $down1 = new Down();
        $down1->setId(1);
        $down1->setSa($sa2);
        $down1->setReason("Problème de données pour l'{$sa2->getName()}");
        $down1->setTemperature(true);
        $down1->setHumidity(false);
        $down1->setCO2(true);
        $down1->setMicrocontroller(true);
        $down1->setDate(new \DateTime('2024-12-01 08:00:00'));
        $manager->persist($down1);

        // SA 2
        $down2 = new Down();
        $down2->setId(2);
        $down2->setSa($sa2);
        $down2->setReason("Raison 1 pour SA 2");
        $down2->setTemperature(false);
        $down2->setHumidity(true);
        $down2->setCO2(false);
        $down2->setMicrocontroller(true);
        $down2->setDate(new \DateTime('2024-12-05 14:30:00'));
        $manager->persist($down2);

        $down3 = new Down();
        $down3->setId(3);
        $down3->setSa($sa2);
        $down3->setReason("Raison 2 pour SA 2");
        $down3->setTemperature(true);
        $down3->setHumidity(false);
        $down3->setCO2(true);
        $down3->setMicrocontroller(false);
        $down3->setDate(new \DateTime('2024-12-10 11:45:00'));
        $manager->persist($down3);

        // SA 4
        $down4 = new Down();
        $down4->setId(4);
        $down4->setSa($sa4);
        $down4->setReason("Raison 1 pour SA 4");
        $down4->setTemperature(false);
        $down4->setHumidity(false);
        $down4->setCO2(true);
        $down4->setMicrocontroller(true);
        $down4->setDate(new \DateTime('2024-12-15 09:20:00'));
        $manager->persist($down4);

        // SA 5
        $down5 = new Down();
        $down5->setId(5);
        $down5->setSa($sa5);
        $down5->setReason("Raison 1 pour SA 5");
        $down5->setTemperature(true);
        $down5->setHumidity(false);
        $down5->setCO2(true);
        $down5->setMicrocontroller(false);
        $down5->setDate(new \DateTime('2024-12-20 16:00:00'));
        $manager->persist($down5);

        $down6 = new Down();
        $down6->setId(6);
        $down6->setSa($sa5);
        $down6->setReason("Raison 2 pour SA 5");
        $down6->setTemperature(false);
        $down6->setHumidity(true);
        $down6->setCO2(false);
        $down6->setMicrocontroller(true);
        $down6->setDate(new \DateTime('2024-12-25 10:15:00'));
        $manager->persist($down6);

        $down6 = new Down();
        $down6->setId(7);
        $down6->setSa($sa5);
        $down6->setReason("Raison 3 pour SA 5");
        $down6->setTemperature(true);
        $down6->setHumidity(true);
        $down6->setCO2(true);
        $down6->setMicrocontroller(false);
        $down6->setDate(new \DateTime('2025-01-03 16:34:56'));
        $manager->persist($down6);
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
