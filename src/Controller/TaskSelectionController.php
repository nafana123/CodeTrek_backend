<?php

namespace App\Controller;

use App\Entity\DifficultyLevels;
use App\Entity\Language;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskSelectionController extends AbstractController
{
    private $entityManager;

    private $taskRepository;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;

        $this->taskRepository = $taskRepository;
    }

    #[Route("/api/languages-and-difficulties", name: "languages_and_difficulties", methods: ["GET"])]
    public function getLanguagesAndDifficulties()
    {
        $languages = $this->entityManager->getRepository(Language::class)->findAll();
        $difficultyLevels = $this->entityManager->getRepository(DifficultyLevels::class)->findAll();

        $languagesData = [];
        foreach ($languages as $language) {
            $languagesData[] = [
                'id' => $language->getId(),
                'name' => $language->getName(),
            ];
        }

        $difficultyData = [];
        foreach ($difficultyLevels as $level) {
            $difficultyData[] = [
                'id' => $level->getId(),
                'name' => $level->getLevel(),
            ];
        }

        return new JsonResponse([
            'languages' => $languagesData,
            'difficulties' => $difficultyData,
        ]);
    }
    #[Route("/api/tasks", name: "tasks", methods: ["GET"])]
    public function getTasksByLanguageAndDifficulty(Request $request)
    {
        $languageId = $request->query->get('language_id');
        $difficultyId = $request->query->get('difficulty_id');

        $tasks = $this->taskRepository->findBy(['language' => $languageId, 'difficulty' => $difficultyId]);

        $tasksData = [];
        foreach ($tasks as $task) {
            $tasksData[] = [
                'id' => $task->getTaskId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
            ];
        }

        return new JsonResponse($tasksData);
    }


}
