<?php

namespace App\Repository;

use App\Entity\ChildMeasurementsHeight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ChildMeasurementsHeight|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChildMeasurementsHeight|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChildMeasurementsHeight[]    findAll()
 * @method ChildMeasurementsHeight[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChildMeasurementsHeightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildMeasurementsHeight::class);
    }

    // /**
    //  * @return ChildMeasurementsHeight[] Returns an array of ChildMeasurementsHeight objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChildMeasurementsHeight
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
