<?php

namespace App\Repository;

use App\Entity\Etudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etudiant>
 *
 * @method Etudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etudiant[]    findAll()
 * @method Etudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etudiant::class);
    }

    public function prepareQueryForPagination(array $filtre){
        $query = $this->createQueryBuilder('e')
        ->andWhere('e.isArchived =false')
        ->leftJoin('e.inscriptions', 'i')
        ->andWhere('i.isArchived =false')
        ->leftJoin('i.anneeScolaire','an')
        ->andWhere('an.id = :idA')
        ->setParameter('idA', $filtre['anneeScolaire']);

        if (!empty($filtre['classe'])) {
            $query = $query->leftJoin('i.classe','c')
            ->andWhere('c.id =:idClasse')
            ->setParameter('idClasse', $filtre['classe']);
        }
        return $query;
    }

    public function findEtudiantWithAbsence(int $annee){
        return $this->createQueryBuilder('e')
           ->andWhere('e.isArchived = false')
           ->leftJoin('e.absences','a')
            ->leftJoin('a.session', 's')
            ->leftJoin('s.cours', 'c')
            ->leftJoin('c.anneeScolaire', 'an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA', $annee)
           ->orderBy('e.id', 'ASC')
           //->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
    }

//    /**
//     * @return Etudiant[] Returns an array of Etudiant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Etudiant
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
