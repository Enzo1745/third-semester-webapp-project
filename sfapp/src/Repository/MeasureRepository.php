<?php

namespace App\Repository;

use App\Entity\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Sa;

/**
 * @extends ServiceEntityRepository<Measure>
 */
class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    //    /**
    //     * @return Measure[] Returns an array of Measure objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Measure
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findBySaOrderedByDate(Sa $sa): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.sa = :sa')
            ->setParameter('sa', $sa)
            ->orderBy('m.captureDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTypeAndSa(string $type, int $saIdInt): array
    {
        $saId = (string) $saIdInt;
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.sa = :saId')
            ->setParameter('type', $type)
            ->setParameter('saId', $saId)
            ->orderBy('m.captureDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
