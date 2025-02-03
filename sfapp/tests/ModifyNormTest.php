<?php

namespace App\Tests;

use App\Entity\Norm;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ModifyNormTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Purge la base de données pour chaque test
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testModifySummerComfortNorms(): void
    {
        // make
        $norm = new Norm();
        $norm->setNormSeason(NormSeason::Summer)
            ->setNormType(NormType::Comfort)
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        // modify
        $norm->setHumidityMinNorm(35)
            ->setHumidityMaxNorm(75)
            ->setTemperatureMinNorm(20)
            ->setTemperatureMaxNorm(28)
            ->setCo2MinNorm(450)
            ->setCo2MaxNorm(1100);
        $this->entityManager->flush();

        // Assert
        $this->assertEquals(35, $norm->getHumidityMinNorm());
        $this->assertEquals(75, $norm->getHumidityMaxNorm());
        $this->assertEquals(20, $norm->getTemperatureMinNorm());
        $this->assertEquals(28, $norm->getTemperatureMaxNorm());
        $this->assertEquals(450, $norm->getCo2MinNorm());
        $this->assertEquals(1100, $norm->getCo2MaxNorm());
    }

    public function testModifyWinterComfortNorms(): void
    {
        // Arrange
        $norm = new Norm();
        $norm->setNormSeason(NormSeason::Winter)
            ->setNormType(NormType::Comfort)
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        // modify
        $norm->setHumidityMinNorm(40)
            ->setHumidityMaxNorm(80)
            ->setTemperatureMinNorm(22)
            ->setTemperatureMaxNorm(30)
            ->setCo2MinNorm(500)
            ->setCo2MaxNorm(1200);
        $this->entityManager->flush();

        // Assert
        $this->assertEquals(40, $norm->getHumidityMinNorm());
        $this->assertEquals(80, $norm->getHumidityMaxNorm());
        $this->assertEquals(22, $norm->getTemperatureMinNorm());
        $this->assertEquals(30, $norm->getTemperatureMaxNorm());
        $this->assertEquals(500, $norm->getCo2MinNorm());
        $this->assertEquals(1200, $norm->getCo2MaxNorm());
    }

    public function testModifySummerTechnicalNorms(): void
    {
        // make
        $norm = new Norm();
        $norm->setNormSeason(NormSeason::Summer)
            ->setNormType(NormType::Technical)
            ->setHumidityMinNorm(20)
            ->setHumidityMaxNorm(60)
            ->setTemperatureMinNorm(16)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(300)
            ->setCo2MaxNorm(900);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        // modify
        $norm->setHumidityMinNorm(25)
            ->setHumidityMaxNorm(65)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(24)
            ->setCo2MinNorm(350)
            ->setCo2MaxNorm(950);
        $this->entityManager->flush();

        // Assert
        $this->assertEquals(25, $norm->getHumidityMinNorm());
        $this->assertEquals(65, $norm->getHumidityMaxNorm());
        $this->assertEquals(18, $norm->getTemperatureMinNorm());
        $this->assertEquals(24, $norm->getTemperatureMaxNorm());
        $this->assertEquals(350, $norm->getCo2MinNorm());
        $this->assertEquals(950, $norm->getCo2MaxNorm());
    }

    public function testModifyWinterTechnicalNorms(): void
    {
        // Arrange
        $norm = new Norm();
        $norm->setNormSeason(NormSeason::Winter)
            ->setNormType(NormType::Technical)
            ->setHumidityMinNorm(25)
            ->setHumidityMaxNorm(55)
            ->setTemperatureMinNorm(15)
            ->setTemperatureMaxNorm(20)
            ->setCo2MinNorm(350)
            ->setCo2MaxNorm(850);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        // modify
        $norm->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(60)
            ->setTemperatureMinNorm(17)
            ->setTemperatureMaxNorm(22)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(900);
        $this->entityManager->flush();

        // Assert
        $this->assertEquals(30, $norm->getHumidityMinNorm());
        $this->assertEquals(60, $norm->getHumidityMaxNorm());
        $this->assertEquals(17, $norm->getTemperatureMinNorm());
        $this->assertEquals(22, $norm->getTemperatureMaxNorm());
        $this->assertEquals(400, $norm->getCo2MinNorm());
        $this->assertEquals(900, $norm->getCo2MaxNorm());
    }
}
