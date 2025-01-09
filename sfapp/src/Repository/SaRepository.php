<?php

namespace App\Repository;

use App\Entity\Sa;
use App\Repository\Model\SAState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\DiagnocticService;

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


    public function findSaByState(SAState $state): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.state = :state')
            ->setParameter('state', $state->value)
            ->getQuery()
            ->getResult();
    }


    public function sortByState(array $saList,int $choice,
                                NormRepository $normRepository,
                                DiagnocticService $diagnosticService
                                ): array
    {


        if ($choice === 1) {
            $order = [
                SAState::Down->value,
                SAState::Waiting->value,
                SAState::Available->value,
                SAState::Installed->value,
            ];

            usort($saList, function($a, $b) use ($order) {
                return array_search($a->getState()->value, $order)
                    <=> array_search($b->getState()->value, $order);
            });
        }

        elseif ($choice === 2) {
            $order = [
                 'red',
                'yellow',
                'green',
                'grey'
            ];



            usort($saList, function($a, $b) use ($order) {
                return array_search($a->getDiagnosticStatus(), $order)
                    <=> array_search($b->getDiagnosticStatus(), $order);
            });


        }



        return $saList;
    }

    public function findAllSortedByName(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC') // Tri ascendant par name
            ->getQuery()
            ->getResult();
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
