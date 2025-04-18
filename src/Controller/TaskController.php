<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\FavoriteTask;
use App\Entity\Language;
use App\Entity\Leaderboard;
use App\Entity\SolvedTask;
use App\Entity\Task;
use App\Entity\TaskLanguage;
use App\Entity\TestCase;
use App\Repository\DiscussionRepository;
use App\Repository\LeaderboardRepository;
use App\Repository\ReplyToMessageRepository;
use App\Repository\SolvedTaskRepository;
use App\Repository\TaskLanguageRepository;
use App\Repository\TestCaseRepository;
use App\Service\CodeExecutionService;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CodeExecutionService $codeExecutionService;
    private TaskLanguageRepository $taskLanguageRepository;
    private LeaderboardRepository $leaderboardRepository;
    private DiscussionRepository $discussionRepository;
    private ReplyToMessageRepository $replyToMessageRepository;
    private TestCaseRepository $testCaseRepository;
    private SolvedTaskRepository $solvedTaskRepository;
    private TaskService $taskService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CodeExecutionService $codeExecutionService,
        TaskLanguageRepository $taskLanguageRepository,
        LeaderboardRepository $leaderboardRepository,
        DiscussionRepository $discussionRepository,
        ReplyToMessageRepository $replyToMessageRepository,
        TestCaseRepository $testCaseRepository,
        SolvedTaskRepository $solvedTaskRepository,
        TaskService $taskService
    )
    {
        $this->entityManager = $entityManager;
        $this->codeExecutionService = $codeExecutionService;
        $this->taskLanguageRepository = $taskLanguageRepository;
        $this->leaderboardRepository = $leaderboardRepository;
        $this->discussionRepository = $discussionRepository;
        $this->replyToMessageRepository = $replyToMessageRepository;
        $this->testCaseRepository = $testCaseRepository;
        $this->solvedTaskRepository = $solvedTaskRepository;
        $this->taskService = $taskService;
    }

    #[Route('/api/task/{id}/{language}', name: 'task', methods: ['GET'])]
    public function displayTask(string $id, string $language): Response
    {
        $language = ($language === 'c') ? 'c#' : $language;

        $task = $this->entityManager->getRepository(Task::class)->find($id);
        $language = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $language]);
        $taskLanguage = $this->taskLanguageRepository->findOneBy(['language' => $language, 'task' => $task]);

        return $this->json([
            'id' => $task->getTaskId(),
            'difficulty' => $task->getDifficulty()->getLevel(),
            'language' => $language->getName(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'input' => $task->getInput(),
            'output' => $task->getOutput(),
            'codeTemplates' => $taskLanguage ? $taskLanguage->getCodeTemplates() : '',
        ]);
    }

    #[Route('/api/execute/task/{id}/{language}', name: 'task_execute', methods: ['POST'])]
    public function executeCode(Request $request, Task $task, string $language): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        $language = ($language === 'c') ? 'c#' : $language;

        $testCase = $task->getTestCase();
        $taskLanguages = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $language]);
        $taskLanguage = $this->entityManager->getRepository(TaskLanguage::class)->findOneBy(['task' => $task, 'language' => $taskLanguages]);

        $outputFunction = $taskLanguage->getExecutionTemplate();

        $userCode = $this->taskService->prepareUserCode($language, $testCase, $code, $taskLanguage->getExecutionTemplate(), $outputFunction);

        [$output] = $this->codeExecutionService->executeUserCode($userCode, $language);

        $answer = $task->getAnswer();

        if(trim($output) === $answer) {
            return $this->json(['success' => true, 'output' => $answer]);
        }

        return $this->json(['success' => false, 'error' => 'Синтаксическая ошибка: ' . $output]);
    }

    #[Route('/api/submit/task/{id}/{language}', name: 'submit_solution', methods: ['POST'])]
    public function submitSolution(string $id, Request $request, $language): Response
    {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $code = $data['code'];

        $language = ($language === 'c') ? 'c#' : $language;

        $taskLanguages = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $language]);
        $taskLanguage = $this->entityManager->getRepository(TaskLanguage::class)->findOneBy(['task' => $id, 'language' => $taskLanguages]);

        $task = $taskLanguage->getTask();
        $testCases = $this->testCaseRepository->findBy(['task' => $task]);

        $outputFunction = $taskLanguage->getExecutionTemplate();

        if ($testCases) {
            foreach ($testCases as $testCase) {
                $expectedOutput = $testCase->getExpectedOutput();

                $userCode = $this->taskService->prepareUserCode($language, $testCase->getInput(), $code, $taskLanguage->getExecutionTemplate(), $outputFunction);
                [$output] = $this->codeExecutionService->executeUserCode($userCode, $language);

                if (trim($output) !== $expectedOutput) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Код не прошел проверку тестов. Пожалуйста, проверьте ваше решение.'
                    ]);
                }
            }
        }

        $existingSolution = $this->entityManager->getRepository(SolvedTask::class)
            ->findOneBy([
                'taskLanguage' => $taskLanguage,
                'user' => $user
            ]);

        if ($existingSolution) {
            $existingSolution->setCode($code);
        } else {
            $solvedTask = new SolvedTask();
            $solvedTask->setCode($code);
            $solvedTask->setTaskLanguage($taskLanguage);
            $solvedTask->setUser($user);

            $this->entityManager->persist($solvedTask);

            $this->updateLeaderboard($user, $task->getDifficulty()->getLevel());
        }

        return $this->json(['success' => true]);
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
                $discussions = $this->discussionRepository->findBy(['task' => $taskId]);
                $totalMessages = count($discussions);

                foreach ($discussions as $discussion) {
                    $replies = $this->replyToMessageRepository->findBy(['discussion' => $discussion]);
                    $totalMessages += count($replies);
                }
                $solvedTasks = $this->solvedTaskRepository->solvedTasksByUserAndLanguage($user, $taskId);

                $groupedTasks[$taskId] = [
                    'id' => $taskId,
                    'title' => $taskLanguage['title'],
                    'description' => $taskLanguage['description'],
                    'input' => $taskLanguage['input'],
                    'output' => $taskLanguage['output'],
                    'difficulty' => $taskLanguage['difficulty'],
                    'isFavorite' => in_array($taskId, $favoriteTaskIds),
                    'totalMessages' => $totalMessages,
                    'solvedTasks' => count($solvedTasks),
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

    #[Route('/api/details/task/{id}', name: 'task_details', methods: ['GET'])]
    public function getTaskDetails($id): Response
    {
        $user = $this->getUser();
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        $taskLanguages = $this->taskLanguageRepository->activeLanguage($id, $user);
        $favoriteTasks = $this->entityManager->getRepository(FavoriteTask::class)

            ->findBy(['user' => $user]);
        $favoriteTaskIds = array_map(function($favorite) {
            return $favorite->getTask()->getTaskId();
        }, $favoriteTasks);

        $taskDetails = [
            'task' => $task,
            'languages' => $taskLanguages,
            'isFavorite' => in_array($task->getTaskId(), $favoriteTaskIds),

        ];

        return $this->json($taskDetails);
    }

    #[Route('/api/user/solution/{id}', name: 'user_solution', methods: ['GET'])]
    public function userSolution(Task $task): Response
    {
        $user = $this->getUser();

        $solvedTasks = $this->solvedTaskRepository->solvedTasksByUserAndLanguage($user, $task);

        return $this->json($solvedTasks);
    }

    private function updateLeaderboard($user, int $points)
    {
        $leaderboardEntry = $this->leaderboardRepository->findOneBy(['user' => $user]);

        if ($leaderboardEntry) {
            $leaderboardEntry->setPoints($leaderboardEntry->getPoints() + $points);
        } else {
            $leaderboardEntry = new Leaderboard();
            $leaderboardEntry->setUser($user);
            $leaderboardEntry->setPoints($points);
            $this->entityManager->persist($leaderboardEntry);
        }

        $this->entityManager->flush();
    }

}