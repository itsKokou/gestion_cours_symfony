<?php

namespace App\Repository;


use App\Entity\Professeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Professeur>
 *
 * @method Professeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Professeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Professeur[]    findAll()
 * @method Professeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Professeur::class);
    }
    

//    /**
//     * @return Professeur[] Returns an array of Professeur objects
//     */

    public function prepareQueryForpagination(array $filtre){
        $query = $this->createQueryBuilder('p')
        ->andWhere('p.isArchived =false')
        ->leftJoin('p.enseignements','e')
        ->andWhere('e.professeur=p');

        if (!empty($filtre['grade'])) {
            $query = $query->andWhere('p.grade =:grade')
            ->setParameter('grade', $filtre['grade']);
        }

        if (!empty($filtre['classe'])) {
            $query = $query->leftJoin('e.classes','c')
            ->andWhere('c.id =:idClasse')
            ->setParameter('idClasse', $filtre['classe']);
           
        }

        if (!empty($filtre['module'])) {
            $query = $query->leftJoin('e.modules','m')
            ->andWhere('m.id =:idModule')
            ->setParameter('idModule', $filtre['module']);
        }
        return $query;
    }

    public function findProfesseurByModule(int $idM){
        return $this->createQueryBuilder('p')
            ->andWhere('p.isArchived =false')
            ->leftJoin('p.enseignements', 'e')
            ->leftJoin('e.modules','m')
            ->andWhere('m.id =:idM')
            ->setParameter('idM',$idM)
            ->getQuery()
            ->getResult();
    }

    //excepté le prof du cours 
    public function findProfesseurByModuleAndClasse(int $idM, array $classes, int $idP){
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.isArchived =false')
            ->andWhere('p.id !=:idP')
            ->setParameter('idP',$idP)
            ->leftJoin('p.enseignements', 'e')
            ->leftJoin('e.classes', 'cl')
            ->leftJoin('e.modules','m')
            ->andWhere('m.id =:idM')
            ->setParameter('idM',$idM);

        for ($i=0; $i < sizeof($classes); $i++) {
            $query 
                ->andWhere('cl.id =:idC')
                ->setParameter('idC', $classes[$i]->getId());
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function getGrades(){
        $results = $this->createQueryBuilder('p')
        ->select('DISTINCT p.grade ')
        ->getQuery()
        ->getScalarResult();
        $grades = [];
        // dd($results);
        foreach($results as $grade){
            $grades[$grade['grade']] = $grade['grade'];
        }
        return $grades;
    }

    // /**
    // * @return Professeur[] Returns an array of Professeur objects
    // */
    // public function findPaginateByFiltre(array $filtre): array {

    //     $query = $this->prepareQueryForpagination($filtre);
        
    //     $offset = ($page-1)*$nbrElt;
    //    return  $query
    //    ->setFirstResult($offset)
    //    ->setMaxResults($nbrElt)
    //    ->getQuery()
    //    ->getResult();
    // }

    // public function countProfesseurByFiltre( array $filtre){
    //     $query =$this->prepareQueryForpagination($filtre);
    //     return $query
    //     ->select('count(p.id) as count')
    //     ->getQuery()
    //     ->getSingleScalarResult();
 
    // }

    // public function countProfesseur() { 
    //     return $this->createQueryBuilder('p')
    //        ->select('count(p.id) as count')
    //        ->andWhere('p.isArchived =false')
    //        ->getQuery()
    //        ->getSingleScalarResult()
    //    ;
    // }


//    public function findOneBySomeField($value): ?Professeur
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
