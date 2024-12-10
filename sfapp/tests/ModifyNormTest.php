<?php

namespace App\Tests;

use App\Entity\Norm;
use App\Repository\NormRepository;
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
    }
    public function testModifyValidNorm(): void
    {
        $norm = new Norm();
        $norm->setSeason('summer')
            ->setHumidityMinNorm(30)
            ->setHumidityMaxNorm(70)
            ->setTemperatureMinNorm(18)
            ->setTemperatureMaxNorm(25)
            ->setCo2MinNorm(400)
            ->setCo2MaxNorm(1000);
        $this->entityManager->persist($norm);
        $this->entityManager->flush();

        $norm->setHumidityMinNorm(35)
            ->setHumidityMaxNorm(75)
            ->setTemperatureMinNorm(20)
            ->setTemperatureMaxNorm(28)
            ->setCo2MinNorm(450)
            ->setCo2MaxNorm(1100);
        $this->entityManager->flush();

        $retrievedNorm = $this->normRepository->findOneBy(['season' => 'summer']);

        $this->assertEquals(35, $retrievedNorm->getHumidityMinNorm());
        $this->assertEquals(75, $retrievedNorm->getHumidityMaxNorm());
        $this->assertEquals(20, $retrievedNorm->getTemperatureMinNorm());
        $this->assertEquals(28, $retrievedNorm->getTemperatureMaxNorm());
        $this->assertEquals(450, $retrievedNorm->getCo2MinNorm());
        $this->assertEquals(1100, $retrievedNorm->getCo2MaxNorm());
    }
}
