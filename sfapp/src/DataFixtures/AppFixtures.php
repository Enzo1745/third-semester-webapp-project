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

    }
}
