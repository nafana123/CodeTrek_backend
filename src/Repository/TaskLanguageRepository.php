<?php

namespace App\Repository;

use App\Entity\TaskLanguage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskLanguage>
 *
 * @method TaskLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskLanguage[]    findAll()
 * @method TaskLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskLanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskLanguage::class);
    }

    public function selectTasks($languageIds)
    {
        $qb = $this->createQueryBuilder('tl');
        $qb
            ->innerJoin('tl.task', 't')
            ->innerJoin('tl.language', 'l')
            ->where('l.id IN (:languageIds)')
            ->setParameter('languageIds', $languageIds);

        return $qb->getQuery()->getResult();
    }


}
