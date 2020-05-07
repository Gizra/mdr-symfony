<?php

namespace App\Repository;

use App\Entity\Adult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Adult|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adult|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adult[]    findAll()
 * @method Adult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adult::class);
    }

    // /**
    //  * @return Adult[] Returns an array of Adult objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Adult
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
