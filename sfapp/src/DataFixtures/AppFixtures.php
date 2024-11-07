<?php

namespace App\DataFixtures;

use App\Entity\Salle;
use App\Entity\Sa;
use App\Repository\Model\EtatSA;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $sa1 = new Sa();
        $sa1->setEtat(EtatSA::Dispo);
        $manager->persist($sa1);

        $sa2 = new Sa();
        $sa2->setEtat(EtatSA::Dispo);
        $manager->persist($sa2);

        $salle1 = new Salle();
        $salle1->setNomSalle("D204");
        $manager->persist($salle1);

        $sa3 = new Sa();
        $sa3->setEtat(EtatSA::Fonctionnel);
        $sa3->setSalle($salle1);
        $manager->persist($sa3);

        $salle2 = new Salle();
        $salle2->setNomSalle("D205");
        $manager->persist($salle2);

        $salle3 = new Salle();
        $salle3->setNomSalle("D206");
        $manager->persist($salle3);

        $manager->flush();
    }
}
