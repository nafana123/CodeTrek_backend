<?php

namespace App\Controller;

use App\Entity\SolvedTask;
use App\Entity\Task;
use App\Service\CodeExecutionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CodeExecutionService $codeExecutionService;

    public function __construct(EntityManagerInterface $entityManager, CodeExecutionService $codeExecutionService)
    {
        $this->entityManager = $entityManager;

        $this->codeExecutionService = $codeExecutionService;
    }

    #[Route('/api/task/{id}/{language}', name: 'task', methods: ['GET'])]
    public function displayTask(string $id, string $language): Response
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        return $this->json([
            'id' => $task->getTaskId(),
            'difficulty' => $task->getDifficulty()->getLevel(),
            'language' => $language,
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'input' => $task->getInput(),
            'output' => $task->getOutput(),
        ]);
    }

    #[Route('/api/execute/task/{id}', name: 'task_execute', methods: ['POST'])]
    public function executeCode(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        [$output, $error] = $this->codeExecutionService->executeUserCode($code);

        if ($error) {
            return $this->json(['success' => false, 'error' => 'Синтаксическая ошибка: ' . $error]);
        }

        return $this->json(['success' => true, 'output' => $output]);
    }

    #[Route('/api/submit/task/{id}', name: 'submit_solution', methods: ['POST'])]
    public function sumbitSolution(string $id, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'];

        [$output, $error] = $this->codeExecutionService->executeUserCode($code);

        if ($error) {
            return $this->json(['success' => false, 'error' => 'Синтаксическая ошибка: ' . $error]);
        }

        $task = $this->entityManager->getRepository(Task::class)->find($id);
        $answer = str_replace("\n", '', $output);
        if ($task->getAnswer() === $answer) {
            $solvedTask = new SolvedTask();
            $solvedTask->setCode($code);
            $solvedTask->setTask($task);
            $solvedTask->setUser($this->getUser());

            $this->entityManager->persist($solvedTask);
            $this->entityManager->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['warning']);
    }
}