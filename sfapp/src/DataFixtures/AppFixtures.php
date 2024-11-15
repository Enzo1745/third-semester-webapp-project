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
        $sa1 = new Sa();
        $sa1->setEtat(SAState::Fonctionnel);
        $sa1->setTemperature(25);
        $sa1->setHumidite(90);
        $sa1->setCO2(1200);
        $manager->persist($sa1);

        $sa2 = new Sa();
        $sa2->setEtat(SAState::Dispo);
        $manager->persist($sa2);

        $sa3 = new Sa();
        $sa3->setEtat(SAState::Fonctionnel);
        $sa3->setTemperature(22);
        $sa3->setHumidite(70);
        $sa3->setCO2(1000);
        $manager->persist($sa3);

        $salle1 = new Room();
        $salle1->setNumSalle("D204");
        $salle1->setIdSA($sa1);
        $manager->persist($salle1);

        $salle2 = new Room();
        $salle2->setNumSalle("D205");
        $salle2->setIdSA($sa3);
        $manager->persist($salle2);

        $salle3 = new Room();
        $salle3->setNumSalle("D206");
        $manager->persist($salle3);

        $sa4 = new Sa();
        $sa4->setEtat(SAState::Dispo);
        $manager->persist($sa4);

        /*$salle4 = new Room();
        $salle4->setNomSalle("D304");
        $manager->persist($salle4);
        */
        $manager->flush();
    }
}
