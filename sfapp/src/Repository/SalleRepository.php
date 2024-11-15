<?php

namespace App\Repository;

use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salle>
 */
class SalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    public function findAllOrderedByNumSalle(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.NumSalle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByNumSalle(string $NumSalle): ?Salle
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.NumSalle = :NumSalle')
            ->setParameter('NumSalle', $NumSalle)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //the request that select only the rooms with an Aquisition system
    public function findAllWithIdSA(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.IdSA IS NOT NULL')
            ->orderBy('s.NumSalle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //the request that select only the rooms without an Aquisition system
    public function findAllWithoutIdSA(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.IdSA IS NULL')
            ->orderBy('s.NumSalle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Salle[] Returns an array of Salle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Salle
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
