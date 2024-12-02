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

        $sql = 'SELECT COUNT(s.id) FROM sa s WHERE s.state = :state';

        $resultSet = $conn->executeQuery($sql, ['state' => 'Disponible']);

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
    }}