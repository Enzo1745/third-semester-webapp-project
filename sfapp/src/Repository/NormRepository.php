<?php

namespace App\Repository;

use App\Entity\Norm;
use App\Repository\Model\NormSeason;
use App\Repository\Model\NormType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Norm>
 */
class NormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Norm::class);
    }

    public function findBySeason(NormSeason $season, NormType $type = NormType::Comfort): ?Norm
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.NormSeason = :season')
            ->andWhere('n.NormType = :type')
            ->setParameter('season', $season)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByType(NormType $type): ?Norm
    {
        return $this->findOneBy(['NormType' => $type]);
    }


    public function findTechnicalLimitsBySeason(NormSeason $season, NormType $type): ?Norm
    {
        return $this->createQueryBuilder('n')
            ->where('n.NormSeason = :season')
            ->andWhere('n.NormType = :type')
            ->setParameter('season', $season->value)
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getOneOrNullResult();
    }



    //    /**
    //     * @return Norm[] Returns an array of Norm objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Norm
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
