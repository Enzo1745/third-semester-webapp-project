<?php

namespace App\Repository;

use App\Entity\Sa;
use App\Repository\Model\SAState;
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

    public function countSa(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
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
    public function countBySaState(SAState $state): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        $stateValue = $state->name;

        $sql = 'SELECT COUNT(s.id) FROM sa s WHERE s.state = :state';

        $resultSet = $conn->executeQuery($sql, ['state' => $state->value]);

        return $resultSet->fetchOne();
    }

    public function findAllIds(): array
    {
        $results = $this->createQueryBuilder('sa')
            ->select('sa.id')
            ->getQuery()
            ->getArrayResult();

        // Transformer les résultats en un tableau de paires clé-valeur
        return array_column($results, 'id', 'id');
    }
}
