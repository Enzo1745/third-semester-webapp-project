<?php

namespace App\DataFixtures;

use App\Repository\Model\SaState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Salle;
use App\Entity\Sa;

/**
 * @brief Class containing all the default data for the database
 */
class AppFixtures extends Fixture
{
    /**
     * @brief Load all the default data in the database
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $salle1 = new Salle();
        $salle1->setNomSalle('D204');

        $salle2 = new Salle();
        $salle2->setNomSalle('D205');


        $sa1 = new Sa();
        $sa1->setEtat(SaState::Functional);
        $sa1->setSalle($salle1);

        $sa2 = new Sa();
        $sa2->setEtat(SaState::Functional);
        $sa2->setSalle($salle2);

        $sa3 = new Sa();
        $sa3->setEtat(SaState::Available);

        // Link the data created with our manager
        $manager->persist($salle1);
        $manager->persist($sa1);
        $manager->persist($salle2);
        $manager->persist($sa2);
        $manager->persist($sa3);

        // Write the linked data in our database
        $manager->flush();
    }
}
