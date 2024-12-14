<?php

namespace App\Repository;

use App\Entity\SolvedTask;
use App\Entity\User;
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

    public function findResolvedTasksByUser(User $user): array
    {
        return $this->createQueryBuilder('st')
            ->innerJoin('st.task', 't') // Присоединяем задачи
            ->join('t.difficulty', 'd') // Присоединяем уровень сложности
            ->join('App\Entity\TaskLanguage', 'tl', 'WITH', 'tl.task = t') // Присоединяем TaskLanguage
            ->join('tl.language', 'l') // Присоединяем таблицу Language
            ->andWhere('st.user = :user') // Фильтруем по пользователю
            ->setParameter('user', $user) // Указываем параметр пользователя
            ->select(
                'd.level AS difficulty',
                't.task_id AS id',
                't.title',
                't.description',
                't.input',
                't.output',
                'l.name AS language'
            )
            ->getQuery()
            ->getResult();
    }
}
