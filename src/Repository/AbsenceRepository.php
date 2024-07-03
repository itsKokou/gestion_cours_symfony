<?php

namespace App\Repository;

use App\Entity\Absence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Absence>
 *
 * @method Absence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Absence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Absence[]    findAll()
 * @method Absence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    public function findAbsencesByEtudiantAndAnneeScolaire(array $filtre)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isArchived = :val')
            ->setParameter('val', false)
            ->leftJoin('a.etudiant','e')
            ->andWhere('e.id = :idEtu')
            ->setParameter('idEtu',$filtre["etudiant"])
            ->leftJoin('a.session', 's')
            ->leftJoin('s.cours','c')
            ->leftJoin('c.anneeScolaire','an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA',$filtre["anneeEncours"])
            ->getQuery()
            ->getResult();
        ;
    }


    public function prepareQueryForPagination(array $filtre)
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.isArchived = :val')
            ->setParameter('val', false)
            ->leftJoin('a.etudiant','e')
            ->leftJoin('a.session', 's')
            ->leftJoin('s.cours','c')
            ->leftJoin('c.semestre','se')
            ->leftJoin('c.anneeScolaire','an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA',$filtre["anneeScolaire"]);

        if (!empty($filtre['etudiant'])) {
            $query = $query->andWhere('e.id = :idEtu')
            ->setParameter('idEtu',$filtre["etudiant"]);
        }

        if (!empty($filtre['semestre'])) {
            $query = $query
                ->andWhere('se.id =:idS')
                ->setParameter('idS', $filtre['semestre']);

        }
        return $query;
    }

//    /**
//     * @return Absence[] Returns an array of Absence objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Absence
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
