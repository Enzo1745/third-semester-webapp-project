<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @brief Function to get all the rooms, ordered by their number (Ex : D204 < D302)
     * @return array
     */
    public function findAllOrderedByNumSalle(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.NumSalle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @brief Function to find a room by using its number
     * @param string $NumSalle -> the room's number
     * @return Room|null
     */
    public function findByNumSalle(string $NumSalle): ?Room
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.NumSalle = :NumSalle')
            ->setParameter('NumSalle', $NumSalle)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Room[] Returns an array of Room objects
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

    //    public function findOneBySomeField($value): ?Room
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
