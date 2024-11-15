<?php

namespace App\Repository;

use App\Entity\Sa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sa>
 */
class SaRepository extends ServiceEntityRepository
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
     * @brief function to get the number of SA when their state is 'Disponible'
     * @return int|null -> the number of SA available
     * @throws \Doctrine\DBAL\Exception
     */
    public function countBySaState(): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(s.id) FROM sa s WHERE s.etat = :etat';

        $resultSet = $conn->executeQuery($sql, ['etat' => 'Disponible']);

        return $resultSet->fetchOne();
    }
}
