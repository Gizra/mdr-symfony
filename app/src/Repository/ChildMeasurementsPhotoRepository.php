<?php

namespace App\Repository;

use App\Entity\ChildMeasurementsPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ChildMeasurementsPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChildMeasurementsPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChildMeasurementsPhoto[]    findAll()
 * @method ChildMeasurementsPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChildMeasurementsPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildMeasurementsPhoto::class);
    }

    // /**
    //  * @return ChildMeasurementsPhoto[] Returns an array of ChildMeasurementsPhoto objects
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
    public function findOneBySomeField($value): ?ChildMeasurementsPhoto
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
