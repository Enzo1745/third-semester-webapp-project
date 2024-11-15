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

    public function findAllOrderedByRoomNumber(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRoomNumber(string $roomNumber): ?Room
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.roomNumber = :roomNumber')
            ->setParameter('roomNumber', $roomNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }


    //the request that select only the rooms with an Aquisition system
    public function findAllWithIdSA(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.idAS IS NOT NULL')
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //the request that select only the rooms without an Aquisition system
    public function findAllWithoutIdSA(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.idAS IS NULL')
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
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
