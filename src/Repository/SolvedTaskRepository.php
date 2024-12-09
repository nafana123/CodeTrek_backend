<?php

namespace App\Repository;

use App\Entity\SolvedTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SolvedTask>
 *
 * @method SolvedTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method SolvedTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method SolvedTask[]    findAll()
 * @method SolvedTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SolvedTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SolvedTask::class);
    }

//    /**
//     * @return SolvedTask[] Returns an array of SolvedTask objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SolvedTask
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
