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
    public function countBySaState(): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(s.id) FROM sa s WHERE s.etat = :etat';

        $resultSet = $conn->executeQuery($sql, ['etat' => 'Disponible']);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchOne();
    }

    public function findBySalleId(int $salleId): ?Sa
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.salle = :salleId')  // Assure-toi que la relation entre Sa et Salle est correctement définie
            ->setParameter('salleId', $salleId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
