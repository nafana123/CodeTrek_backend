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
    private DiscussionRepository $discussionRepository;
    private ReplyToMessageRepository $replyToMessageRepository;
    private TestCaseRepository $testCaseRepository;

    private SolvedTaskRepository $solvedTaskRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CodeExecutionService $codeExecutionService,
        TaskLanguageRepository $taskLanguageRepository,
        LeaderboardRepository $leaderboardRepository,
        DiscussionRepository $discussionRepository,
        ReplyToMessageRepository $replyToMessageRepository,
        TestCaseRepository $testCaseRepository,
        SolvedTaskRepository $solvedTaskRepository,

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
    }

    #[Route('/api/task/{id}/{language}', name: 'task', methods: ['GET'])]
    public function displayTask(string $id, string $language): Response
    {
        // почему то # в маршруте не передаётся, разобраться почему
        if($language === 'c'){
            $language = 'c#';
        }

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
    public function executeCode(Request $request, string $language): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        if($language === 'c'){
            $language = 'c#';
        }

        [$output, $error] = $this->codeExecutionService->executeUserCode($code, $language);

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

        if ($language === 'c') {
            $language = 'c#';
        }

        $taskLanguages = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $language]);
        $taskLanguage = $this->entityManager->getRepository(TaskLanguage::class)->findOneBy(['task' => $id, 'language' => $taskLanguages]);

        $task = $taskLanguage->getTask();
        $testCases = $this->testCaseRepository->findBy(['task' => $task]);

        //Вынесу в базу когда проверю все языки
        $outputFunctions = [
            'javaScript' => 'console.log(sumArray(%s));',
            'python' => "print(sumArray(%s))",
            'php' => 'echo sumArray(%s);',
            'c++' => 'int main() { std::cout << sumArray(std::vector<int>%s) << std::endl; return 0; }',
            'c#' => 'Console.WriteLine(SumArray(new int[] %s));',
            'java' => '',
            'go' => 'func main() {    fmt.Println(sumArray([]int%s))}',
            'typeScript' => 'console.log(sumArray(%s));',
        ];

        if (!isset($outputFunctions[$language])) {
            return $this->json(['success' => false, 'error' => 'Неизвестный язык']);
        }

        if ($testCases) {
            foreach ($testCases as $testCase) {
                $input = $testCase->getInput();
                $expectedOutput = $testCase->getExpectedOutput();

                if ($language === 'c++' || $language === 'c#' || $language === 'go') {
                    $formattedInput = str_replace(['[', ']'], ['{', '}'], json_encode($input));
                } else if($language === 'java'){
                    $formattedInput = 'new int[]' . str_replace(['[', ']'], ['{', '}'], json_encode($input));
                    $code = preg_replace(
                        '/public class Main \{/',
                        "public class Main {\n    public static void main(String[] args) {\n        System.out.println(sumArray($formattedInput));\n    }\n",
                        $code
                    );
                }
                else{
                    $formattedInput = json_encode($input);
                }

                $outputCode = sprintf($outputFunctions[$language], $formattedInput);


                $userCode = $code . "\n" . $outputCode;


                [$output, $error] = $this->codeExecutionService->executeUserCode($userCode, $language);

                if ($error) {
                    return $this->json(['success' => false]);
                }

                $answer = trim($output);

                if ($answer !== $expectedOutput) {
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
                'user' => $this->getUser()
            ]);

        if ($existingSolution) {
            $existingSolution->setCode($code);
        } else {
            $solvedTask = new SolvedTask();
            $solvedTask->setCode($code);
            $solvedTask->setTaskLanguage($taskLanguage);
            $solvedTask->setUser($this->getUser());

            $this->entityManager->persist($solvedTask);
        }

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

                $groupedTasks[$taskId] = [
                    'id' => $taskId,
                    'title' => $taskLanguage['title'],
                    'description' => $taskLanguage['description'],
                    'input' => $taskLanguage['input'],
                    'output' => $taskLanguage['output'],
                    'difficulty' => $taskLanguage['difficulty'],
                    'isFavorite' => in_array($taskId, $favoriteTaskIds),
                    'totalMessages' => $totalMessages,
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

}