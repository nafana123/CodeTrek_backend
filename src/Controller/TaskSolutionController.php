<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Entity\SolvedTask;
use App\Entity\TaskLanguage;
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


    private SolvedTaskRepository $solvedTaskRepository;



    public function __construct(EntityManagerInterface $entityManager, SolvedTaskRepository $solvedTaskRepository)
    {
        $this->entityManager = $entityManager;


        $this->solvedTaskRepository = $solvedTaskRepository;
    }

    #[Route('/api/output/task/{id}/{language}', name: 'task_output', methods: ['POST', 'GET'])]
    public function taskSolution(string $id, $language): Response
    {
        $user = $this->getUser();

        $taskLanguage = $this->entityManager->getRepository(TaskLanguage::class)
            ->findOneBy([
                'task' => $id,
                'language' => $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $language])
            ]);

        $userSolvedTask = $this->solvedTaskRepository->findOneBy(['user' => $user, 'taskLanguage' => $taskLanguage]);

        return $this->json([
            'userSolvedTask' => $userSolvedTask->getTaskLanguage()->getTask(),
            'userCode' => $userSolvedTask->getCode(),
            'solvedTasksList' => $this->solvedTaskRepository->findBy(['taskLanguage' => $taskLanguage])
        ]);
    }

}
