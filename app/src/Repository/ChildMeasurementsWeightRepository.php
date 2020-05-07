<?php

namespace App\Repository;

use App\Entity\ChildMeasurementsWeight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ChildMeasurementsWeight|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChildMeasurementsWeight|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChildMeasurementsWeight[]    findAll()
 * @method ChildMeasurementsWeight[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChildMeasurementsWeightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildMeasurementsWeight::class);
    }

    // /**
    //  * @return ChildMeasurementsWeight[] Returns an array of ChildMeasurementsWeight objects
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
    public function findOneBySomeField($value): ?ChildMeasurementsWeight
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
