<?php

namespace App\Repository;

use App\Entity\DifficultyLevels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DifficultyLevelsRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifficultyLevels::class);
    }
}