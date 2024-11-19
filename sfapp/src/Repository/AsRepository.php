<?php

namespace App\Repository;

use App\Entity\Sa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sa>
 */
class AsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sa::class);
    }

    //    /**
    //     * @return Sa[] Returns an array of Sa objects
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

    //    public function findOneBySomeField($value): ?Sa
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @brief Function to get the number of sa by their state
     * @return int|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function countBySaState(): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(s.id) FROM sa s WHERE s.state = :state';

        $resultSet = $conn->executeQuery($sql, ['state' => 'Disponible']);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchOne();
    }
}
