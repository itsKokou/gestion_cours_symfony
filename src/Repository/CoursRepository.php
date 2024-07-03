<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 *
 * @method Cours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cours[]    findAll()
 * @method Cours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    public function prepareQueryForPagination(array $filtre)
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.isArchived =false')
            ->leftJoin('c.anneeScolaire','an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA',$filtre['anneeScolaire'])
            ->leftJoin('c.semestre', 's')
            ->leftJoin('s.niveau','n')
            ->leftJoin('c.classes','cl');

        if (!empty($filtre['semestre'])) {
            $query = $query
                ->andWhere('s.id =:idS')
                ->setParameter('idS', $filtre['semestre']);
        }

        if (!empty($filtre['niveau'])) {
            $query = $query
                ->andWhere('n.id =:idN')
                ->setParameter('idN', $filtre['niveau']);
        }

        if ($filtre['classe'][0]!=0) {
            for ($i=0; $i < sizeof($filtre['classe']) ; $i++) { 
                $query = $query
                ->andWhere('cl.id =:idCl')
                ->setParameter('idCl', $filtre['classe'][$i]);
            }
        }

        return $query;
    }

//    /**
//     * @return Cours[] Returns an array of Cours objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cours
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
