<?php

<<<<<<< HEAD
namespace App\Repository;

use App\Entity\User;
=======
namespace templates\Repository;

use App\Entity\Utilisateur;
>>>>>>> US_4-AfficherListeSalles
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
<<<<<<< HEAD
 * @extends ServiceEntityRepository<User>
=======
 * @extends ServiceEntityRepository<Utilisateur>
>>>>>>> US_4-AfficherListeSalles
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
<<<<<<< HEAD
        parent::__construct($registry, User::class);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
=======
        parent::__construct($registry, Utilisateur::class);
    }

    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
>>>>>>> US_4-AfficherListeSalles
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

<<<<<<< HEAD
    //    public function findOneBySomeField($value): ?User
=======
    //    public function findOneBySomeField($value): ?Utilisateur
>>>>>>> US_4-AfficherListeSalles
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
