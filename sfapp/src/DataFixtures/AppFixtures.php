<?php

namespace App\DataFixtures;

use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $salle = new Salle();
        $salle->setNumSalle('SalleTest');
        $manager->persist($salle);
        $manager->flush();


        $salle2 = new Salle();
        $salle2->setNumSalle('SalleTestAvecSa');
        $salle2->setIdSA(1);
        $manager->persist($salle2);
        $manager->flush();

    }
}
