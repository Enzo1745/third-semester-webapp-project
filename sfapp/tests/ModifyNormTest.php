<?php

namespace App\Tests;

use App\Entity\Norm;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use App\Repository\NormRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ModifyNormTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private NormRepository $normRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->normRepository = $this->entityManager->getRepository(Norm::class);

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }
    public function testModifySummerNorms(): void
    {
        $norm = new Norm();
        $norm->setNormSeason(NormSeason::Summer)
            ->setNormType(NormType::Comfort) // norms before changing them
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        //changing the norms
        $norm->setHumidityMinNorm(35)
            ->setHumidityMaxNorm(75)
            ->setTemperatureMinNorm(20)
            ->setTemperatureMaxNorm(28)
            ->setCo2MinNorm(450)
            ->setCo2MaxNorm(1100);
        $this->entityManager->flush();

        $retrievedNorm = $this->normRepository->findBySeason(NormSeason::Summer);

        // check if norms are as expected
        $this->assertEquals(35, $retrievedNorm->getHumidityMinNorm());
        $this->assertEquals(75, $retrievedNorm->getHumidityMaxNorm());
        $this->assertEquals(20, $retrievedNorm->getTemperatureMinNorm());
        $this->assertEquals(28, $retrievedNorm->getTemperatureMaxNorm());
        $this->assertEquals(450, $retrievedNorm->getCo2MinNorm());
        $this->assertEquals(1100, $retrievedNorm->getCo2MaxNorm());
    }

    public function testModifyWinterNorm(): void
    {
        $norm = new Norm();
        $norm->setNormSeason(NormSeason::Winter)
            ->setNormType(NormType::Comfort) // norms before changing them
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        // changing the norms
        $norm->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(30)
            ->setTemperatureMaxNorm(38)
            ->setCo2MinNorm(550)
            ->setCo2MaxNorm(1400);
        $this->entityManager->flush();

        $retrievedNorm = $this->normRepository->findBySeason(NormSeason::Winter);

        // check if norms are as expected
        $this->assertEquals(40, $retrievedNorm->getHumidityMinNorm());
        $this->assertEquals(80, $retrievedNorm->getHumidityMaxNorm());
        $this->assertEquals(30, $retrievedNorm->getTemperatureMinNorm());
        $this->assertEquals(38, $retrievedNorm->getTemperatureMaxNorm());
        $this->assertEquals(550, $retrievedNorm->getCo2MinNorm());
        $this->assertEquals(1400, $retrievedNorm->getCo2MaxNorm());
    }
}
