<?php

namespace App\Repository;

use App\Entity\Tips;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tips>
 * @brief repository used to manage the Entity Tips
 */
class TipsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tips::class);
    }

    public function findRandTips(): ?string // return a random tips from database
    {
        $maxId = $this->createQueryBuilder('t') // select all tips
            ->select('MAX(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if (!$maxId) {
            return null; // if not tips in database
        }

        $randomId = random_int(1, $maxId); // return a random int fom 1 to max of tip number

        $tip = $this->createQueryBuilder('t')
            ->select('t.content') // select content attribut
            ->where('t.id >= :randomId')
            ->setParameter('randomId', $randomId)
            ->setMaxResults(1) // select only one result
            ->getQuery()
            ->getOneOrNullResult();

        return $tip['content'] ?? null; // Return content or null if not result
    }







    //    /**
    //     * @return Tips[] Returns an array of Tips objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tips
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
