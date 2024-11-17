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

    public function findAllOrderedByRoomName(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.roomName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRoomName(string $roomName): ?Room
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.roomName = :roomName')
            ->setParameter('roomName', $roomName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @brief function to return the number of room without SA
     * @return int|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function countBySaAvailable(): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(*)
                FROM room
                LEFT JOIN sa ON room.id = sa.room_id
                WHERE sa.room_id IS NULL;
        ';

        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchOne();
    }
}
