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
}