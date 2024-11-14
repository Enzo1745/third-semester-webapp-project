<?php

namespace App\DataFixtures;

<<<<<<< HEAD
=======
use App\Entity\Salle;
>>>>>>> US_4-AfficherListeSalles
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
<<<<<<< HEAD
=======
        $salle = new Salle();
        $salle->setNumSalle('SalleTest');
        $manager->persist($salle);
        $manager->flush();

>>>>>>> US_4-AfficherListeSalles
    }
}
