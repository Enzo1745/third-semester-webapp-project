<?php

namespace App\Repository;

use App\Entity\Room;
use App\Repository\Model\SAState;
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

    public function findByRoomNameWithSA(string $roomName): ?Room
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.sa', 's')
            ->addSelect('s')
            ->andWhere('r.roomName = :roomName')
            ->andWhere('s.Temperature IS NOT NULL')
            ->andWhere('s.CO2 IS NOT NULL')
            ->andWhere('s.Humidity IS NOT NULL')
            ->setParameter('roomName', $roomName)
            ->setMaxResults(1)
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

    public function findAllOrderedByRoomNumber(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.roomName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRoomNumber(string $roomNumber): ?Room
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.roomName = :roomNumber')
            ->setParameter('roomNumber', $roomNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //the request that select only the rooms with an Aquisition system
    public function findAllWithIdSa(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.idSa IS NOT NULL')
            ->orderBy('r.roomName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //the request that select only the rooms without an Aquisition system
    public function findAllWithoutIdSa(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.idSa IS NULL')
            ->orderBy('r.roomName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les salles dont le SA associé est dans un état donné
     */
    public function findBySaState(SAState $state): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.sa', 's')          // Jointure sur la propriété "sa"
            ->addSelect('s')
            ->andWhere('s.state = :state')   // Filtrer selon l'état du SA
            ->setParameter('state', $state->value)
            ->orderBy('r.roomName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function sortRoomsByState(array $rooms, int $choice): array
    {
        $orderDiaRedFirst = ['red', 'yellow', 'green', 'grey'];
        $orderDiaGreenFirst = ['green', 'yellow', 'red', 'grey'];

        usort($rooms, function($roomA, $roomB) use ($rooms, $choice, $orderDiaGreenFirst, $orderDiaRedFirst) {




                // Diagnostic
                $colorA = $roomA->getDiagnosticStatus() ?: 'grey';
                $colorB = $roomB->getDiagnosticStatus() ?: 'grey';

                if ($choice === 1) {
                    $indexA = in_array($colorA, $orderDiaRedFirst) !== false ? array_search($colorA, $orderDiaRedFirst) : PHP_INT_MAX;
                    $indexB = in_array($colorB, $orderDiaRedFirst) !== false ? array_search($colorB, $orderDiaRedFirst) : PHP_INT_MAX;
                    $cmp = $indexA <=> $indexB;
                } else {
                    return strcasecmp($roomA->getRoomName(), $roomB->getRoomName());
                }


            return $cmp;
        });

        return $rooms;
    }


}