<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SolvedTask;
use App\Repository\SolvedTaskRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskSolutionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private TaskRepository $taskRepository;

    private SolvedTaskRepository $solvedTaskRepository;



    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository, SolvedTaskRepository $solvedTaskRepository)
    {
        $this->entityManager = $entityManager;

        $this->taskRepository = $taskRepository;

        $this->solvedTaskRepository = $solvedTaskRepository;
    }

    #[Route('/api/output/task/{id}', name: 'task_output', methods: ['POST', 'GET'])]
    public function taskSolution(string $id)
    {
        $user = $this->getUser();
        $task = $this->taskRepository->find($id);

        $userSolvedTask = $this->solvedTaskRepository->findOneBy(['user' => $user, 'task' => $task]);
        $solvedTasksList =$this->solvedTaskRepository->findBy(['task' => $task]);


        return $this->json([
            'userSolvedTask' => $userSolvedTask,
            'solvedTasksList' => $solvedTasksList
        ]);
    }
}
