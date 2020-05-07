<?php

namespace App\Repository;

use App\Entity\BackendSyncDownload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BackendSyncDownload|null find($id, $lockMode = null, $lockVersion = null)
 * @method BackendSyncDownload|null findOneBy(array $criteria, array $orderBy = null)
 * @method BackendSyncDownload[]    findAll()
 * @method BackendSyncDownload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BackendSyncDownloadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BackendSyncDownload::class);
    }

    // /**
    //  * @return BackendSyncDownload[] Returns an array of BackendSyncDownload objects
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
    public function findOneBySomeField($value): ?BackendSyncDownload
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
