<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * @return Session[] Returns an array of Session objects
     */
    public function findByClasse($idClasse, $anneeActuelle): array
    {
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.isArchived = false')
            ->leftJoin('s.cours', 'c')
            ->leftJoin('c.anneeScolaire', 'an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA', $anneeActuelle)
        ;

        if ($idClasse != 0) {
            $query
                ->leftJoin('c.classes', 'cl')
                ->andWhere('cl.id = :idClasse')
                ->setParameter('idClasse', $idClasse);
        }
        return $query->getQuery()
            ->getResult();
    }

    public function findSessionByClasseAndWeek($idClasse, $anneeActuelle, $lundi, $samedi): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isArchived = false')
            ->andWhere('s.date >= :lundi')
            ->setParameter("lundi", $lundi)
            ->andWhere('s.date <= :samedi')
            ->setParameter("samedi", $samedi)
            ->leftJoin('s.cours', 'c')
            ->leftJoin('c.anneeScolaire', 'an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA', $anneeActuelle)
            ->leftJoin('c.classes', 'cl')
            ->andWhere('cl.id = :idClasse')
            ->setParameter('idClasse', $idClasse)
            ->getQuery()
            ->getResult();
        ;
    }

    public function findSessionByWeek($anneeActuelle, $lundi, $samedi): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isArchived = false')
            ->andWhere('s.date >= :lundi')
            ->setParameter("lundi", $lundi)
            ->andWhere('s.date <= :samedi')
            ->setParameter("samedi", $samedi)
            ->leftJoin('s.cours', 'c')
            ->leftJoin('c.anneeScolaire', 'an')
            ->andWhere('an.id=:idA')
            ->setParameter('idA', $anneeActuelle)
            ->getQuery()
            ->getResult();
        ;
    }


    //    public function findOneBySomeField($value): ?Session
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
