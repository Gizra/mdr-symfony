<?php

namespace App\Repository;

use App\Entity\BackendSyncUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BackendSyncUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method BackendSyncUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method BackendSyncUpload[]    findAll()
 * @method BackendSyncUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BackendSyncUploadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BackendSyncUpload::class);
    }

    // /**
    //  * @return BackendSyncUpload[] Returns an array of BackendSyncUpload objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BackendSyncUpload
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
