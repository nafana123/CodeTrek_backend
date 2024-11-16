<?php
namespace App\Controller;

use App\Entity\Language;
use App\Entity\Task;
use App\Entity\UserLanguage;
use App\Service\TaskService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class TaskSelectionController extends AbstractController
{
    private $userService;
    private $entityManager;

    private $taskService;
    public function __construct(UserService $userService, EntityManagerInterface $entityManager, TaskService $taskService)
    {
         $this->userService = $userService;

         $this->entityManager = $entityManager;

         $this->taskService = $taskService;
    }
    #[Route("api/language/selection", name: "language_selection", methods: ["POST"])]
    public function languageSelection(Request $request)
    {
        $user = $this->getUser();

        return $this->json(['lang' => $user]);
    }
    #[Route("api/user/languages", name: "user_languages", methods: ["GET"])]
    public function userLanguages(Request $request)
    {
        $user = $this->userService->getUserByToken($request);
        $activeLanguages = $this->taskService->getUserLanguages($user);

        return $this->json(['lang' => $activeLanguages]);

    }
    #[Route("api/choice/tasks", name: "choice_tasks", methods: ["GET"])]
    public function choiceTasks(Request $request)
    {
        $user = $this->userService->getUserByToken($request);

        $activeLanguages = $this->entityManager->getRepository(UserLanguage::class)->findBy(['user' => $user]);

        $languageIds = array_map(function ($userLanguage) {
            return $userLanguage->getLanguage()->getId();
        }, $activeLanguages);

        if (empty($languageIds)) {
            return $this->json(['tasks' => []]);
        }

        $tasks = $this->entityManager->getRepository(Task::class)->findBy([
            'language' => $languageIds
        ]);

        if (empty($tasks)) {
            return $this->json(['tasks' => []]);
        }

        $taskData = array_map(function ($task) {
            return [
                'taskId' => $task->getTaskId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'difficultyLevel' => $task->getDifficulty()->getLevel(),
                'languageName' => $task->getLanguage()->getName(),
            ];
        }, $tasks);

        return $this->json(['tasks' => $taskData]);
    }
}

