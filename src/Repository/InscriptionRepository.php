<?php

namespace App\Repository;

use App\Entity\AnneeScolaire;
use App\Entity\Inscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscription>
 *
 * @method Inscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inscription[]    findAll()
 * @method Inscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Inscription::class);
    }

    public function prepareQueryForpagination(array $filtre){
        $query =  $this->createQueryBuilder('i')
        ->andWhere('i.isArchived =false')
        ->leftJoin('i.anneeScolaire','an')
        ->andWhere('an.id = :idA')
        ->setParameter('idA', $filtre['anneeScolaire'])
        ->leftJoin('i.etudiant','e')
        ->leftJoin('i.classe','c');

        if (!empty($filtre['classe'])) {
            $query = $query
            ->andWhere('c.id =:idClasse')
            ->setParameter('idClasse', $filtre['classe']);
        }

        if (!empty($filtre['date'])) {
            $query = $query
            ->andWhere('i.createAt=:date')
            ->setParameter('date', $filtre['date']);
        }
        return $query;
    }

//    /**
//     * @return Inscription[] Returns an array of Inscription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Inscription
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
