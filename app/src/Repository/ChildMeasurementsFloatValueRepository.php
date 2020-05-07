<?php

namespace App\Repository;

use App\Entity\ChildMeasurementsFloatValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ChildMeasurementsFloatValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChildMeasurementsFloatValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChildMeasurementsFloatValue[]    findAll()
 * @method ChildMeasurementsFloatValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChildMeasurementsFloatValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildMeasurementsFloatValue::class);
    }

    // /**
    //  * @return ChildMeasurementsFloatValue[] Returns an array of ChildMeasurementsFloatValue objects
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
    public function findOneBySomeField($value): ?ChildMeasurementsFloatValue
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
