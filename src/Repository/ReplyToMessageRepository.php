<?php

namespace App\Repository;

use App\Entity\ReplyToMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReplyToMessage>
 *
 * @method ReplyToMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReplyToMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReplyToMessage[]    findAll()
 * @method ReplyToMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReplyToMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReplyToMessage::class);
    }

//    /**
//     * @return ReplyToMessage[] Returns an array of ReplyToMessage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReplyToMessage
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
