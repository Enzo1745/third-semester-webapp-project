<?php

namespace App\Repository;

use App\Entity\Down;
use App\Repository\Model\SAState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Down>
 * @brief repository used to manage the Entity Down 
 */
class DownRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Down::class);
    }

    public function countPannesBySa(int $saId): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.sa = :sa')
            ->setParameter('sa', $saId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPannes(): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Down[] Returns an array of Down objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Down
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findLastDownSa(): array
    {
        return $this->createQueryBuilder('down')
            ->join('down.sa', 'sa')
            ->where('sa.state = :state')
            ->setParameter('state', SAState::Down)
            ->andWhere('down.date = (SELECT MAX(d.date) FROM App\Entity\Down d WHERE d.sa = sa)')  // Sous-requête pour la dernière panne
            ->getQuery()
            ->getResult();
    }
}
