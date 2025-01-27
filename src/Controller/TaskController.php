<?php

namespace App\Controller;

use App\Entity\FavoriteTask;
use App\Entity\Language;
use App\Entity\Leaderboard;
use App\Entity\SolvedTask;
use App\Entity\Task;
use App\Entity\TaskLanguage;
use App\Repository\LeaderboardRepository;
use App\Repository\TaskLanguageRepository;
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

    private TaskLanguageRepository $taskLanguageRepository;

    private LeaderboardRepository $leaderboardRepository;

    public function __construct(EntityManagerInterface $entityManager, CodeExecutionService $codeExecutionService, TaskLanguageRepository $taskLanguageRepository, LeaderboardRepository $leaderboardRepository)
    {
        $this->entityManager = $entityManager;

        $this->codeExecutionService = $codeExecutionService;

        $this->taskLanguageRepository = $taskLanguageRepository;

        $this->leaderboardRepository = $leaderboardRepository;
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

    #[Route('/api/submit/task/{id}/{language}', name: 'submit_solution', methods: ['POST'])]
    public function submitSolution(string $id, Request $request, $language): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'];
        $taskLanguages = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $language]);

        $taskLanguage = $this->entityManager->getRepository(TaskLanguage::class)
            ->findOneBy([
                'task' => $id,
                'language' => $taskLanguages
            ]);

        $task = $taskLanguage->getTask();

        [$output, $error] = $this->codeExecutionService->executeUserCode($code);

        if ($error) {
            return $this->json(['success' => false, 'error' => 'Синтаксическая ошибка: ' . $error]);
        }

        $answer = str_replace("\n", '', $output);
        if ($task->getAnswer() === $answer) {
            $solvedTask = new SolvedTask();
            $solvedTask->setCode($code);
            $solvedTask->setTaskLanguage($taskLanguage);
            $solvedTask->setUser($this->getUser());

            $this->entityManager->persist($solvedTask);
            $this->entityManager->flush();

            $points = $this->leaderboardRepository->findOneBy(['user' => $this->getUser()]);

            if ($points !== null) {
                $points->setPoints($points->getPoints() + $task->getDifficulty()->getLevel());
            } else {
                $points = new Leaderboard();
                $points->setUser($this->getUser());
                $points->setPoints($task->getDifficulty()->getLevel());
                $this->entityManager->persist($points);
            }

            $this->entityManager->flush();

            return $this->json(['success' => $points]);
        }

        return $this->json(['warning']);
    }

    #[Route('/api/all/tasks', name: 'tasks', methods: ['GET'])]
    public function allTasks(): Response
    {
        $user = $this->getUser();

        $taskLanguages = $this->taskLanguageRepository->allTasks($user);

        $favoriteTasks = $this->entityManager->getRepository(FavoriteTask::class)
            ->findBy(['user' => $user]);
        $favoriteTaskIds = array_map(function($favorite) {
            return $favorite->getTask()->getTaskId();
        }, $favoriteTasks);

        $groupedTasks = [];
        foreach ($taskLanguages as $taskLanguage) {
            $taskId = $taskLanguage['id'];
            if (!isset($groupedTasks[$taskId])) {
                $groupedTasks[$taskId] = [
                    'id' => $taskId,
                    'title' => $taskLanguage['title'],
                    'description' => $taskLanguage['description'],
                    'input' => $taskLanguage['input'],
                    'output' => $taskLanguage['output'],
                    'difficulty' => $taskLanguage['difficulty'],
                    'isFavorite' => in_array($taskId, $favoriteTaskIds),
                ];
            }

            $groupedTasks[$taskId]['languages'][] = $taskLanguage['language'];
        }

        return $this->json(array_values($groupedTasks));
    }
    #[Route('/api/tasks/{id}/favorite', name: 'toggle_favorite_task', methods: ['POST'])]
    public function toggleFavorite($id): Response
    {
        $user = $this->getUser();
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        $favoriteTask = $this->entityManager->getRepository(FavoriteTask::class)->findOneBy([
            'user' => $user,
            'task' => $task,
        ]);

        if ($favoriteTask) {
            $this->entityManager->remove($favoriteTask);
            $this->entityManager->flush();

            return $this->json(['success' => true]);
        } else {
            $favoriteTask = new FavoriteTask();
            $favoriteTask->setUser($user);
            $favoriteTask->setTask($task);

            $this->entityManager->persist($favoriteTask);
            $this->entityManager->flush();

            return $this->json(['success' => true]);
        }
    }

}