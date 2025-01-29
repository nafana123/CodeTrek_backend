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

    public function selectTasks($languageIds, $user)
    {
        $qb = $this->createQueryBuilder('tl');
        $qb
            ->innerJoin('tl.task', 't')
            ->innerJoin('tl.language', 'l')
            ->leftJoin('App\Entity\SolvedTask', 'st', 'WITH', 'st.taskLanguage = tl AND st.user = :user')
            ->where('l.id IN (:languageIds)')
            ->andWhere('st.id IS NULL')
            ->setParameter('languageIds', $languageIds)
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function allTasks($user)
    {
        $qb = $this->createQueryBuilder('tl');
        $qb
            ->join('tl.task', 't')
            ->join('tl.language', 'l')
            ->join('t.difficulty', 'd')
            ->leftJoin('App\Entity\SolvedTask', 'st', 'WITH', 'st.taskLanguage = tl AND st.user = :user')
            ->andWhere('st.id IS NULL')
            ->setParameter('user', $user)
            ->select('t.task_id AS id', 't.title', 't.description', 't.input', 't.output', 'd.level AS difficulty', 'l.name AS language', 'st.id AS solved_task_id');

        return $qb->getQuery()->getResult();
    }

    public function activeLanguage($id, $user)
    {
        $qb = $this->createQueryBuilder('tl')
            ->join('tl.task', 't')
            ->join('tl.language', 'l')
            ->leftJoin('App\Entity\SolvedTask', 'st', 'WITH', 'st.taskLanguage = tl AND st.user = :user')
            ->andWhere('t.task_id = :taskId')
            ->andWhere('st.id IS NULL')
            ->setParameter('user', $user)
            ->setParameter('taskId', $id)
            ->select('l.name AS language');

        return $qb->getQuery()->getResult();
    }


}
