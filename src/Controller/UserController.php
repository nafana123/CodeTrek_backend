<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\FavoriteTask;
use App\Repository\LeaderboardRepository;
use App\Repository\SolvedTaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private SolvedTaskRepository $solvedTaskRepository;
    private EntityManagerInterface $entityManager;
    private LeaderboardRepository $leaderboardRepository;

    public function __construct(SolvedTaskRepository $solvedTaskRepository, EntityManagerInterface $entityManager, LeaderboardRepository $leaderboardRepository)
    {
        $this->solvedTaskRepository = $solvedTaskRepository;
        $this->entityManager = $entityManager;
        $this->leaderboardRepository = $leaderboardRepository;
    }
    #[Route('/api/user/profile', name: 'profile', methods: ['GET'])]
    public function getDataUser(): Response
    {
        $user = $this->getUser();
        $leaderboard = $this->leaderboardRepository->findBy([], ['points' => 'DESC']);

        $userRank = 0;
        $userPoints = 0;

        foreach ($leaderboard as $index => $entry) {
            if ($entry->getUser() === $user) {
                $userPoints = $entry->getPoints();
                $userRank = $index + 1;
                break;
            }
        }

        $solvedTasks = $this->solvedTaskRepository->findResolvedTasksByUser($user);

        $responseData = $this->prepareUserData($user, $solvedTasks, $userPoints, $userRank);
        return $this->json($responseData);
    }

    private function prepareUserData($user, array $solvedTasks, int $userPoints, int $userRank): array
    {
        return [
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'email' => $user->getEmail(),
                'registrationDate' => $user->getData(),
                'points' => $userPoints,
                'rank' => $userRank,
            ],
            'solvedTasks' => $this->formatSolvedTasks($solvedTasks),
        ];
    }

    private function formatSolvedTasks(array $solvedTasks): array
    {
        return array_map(static function ($task) {
            return [
                'id' => $task['id'],
                'title' => $task['title'],
                'description' => $task['description'],
                'difficulty' => $task['difficulty'],
                'input' => $task['input'],
                'output' => $task['output'],
                'language' => $task['language'],
            ];
        }, $solvedTasks);
    }

    #[Route('/api/user/favorites', name: 'favorites', methods: ['GET'])]
    public function getFavoriteTasks(): Response
    {
        $user = $this->getUser();

        $favoriteTasks = $this->entityManager->getRepository(FavoriteTask::class)->findBy(['user' => $user]);

        $favoriteTasksData = array_map(function ($favoriteTask) {
            return [
                'id' => $favoriteTask->getTask()->getTaskId(),
                'title' => $favoriteTask->getTask()->getTitle(),
                'difficulty' => $favoriteTask->getTask()->getDifficulty()->getLevel(),
            ];
        }, $favoriteTasks);

        return $this->json($favoriteTasksData);
    }

    #[Route('/api/user/profile/edit', name: 'edit_profile', methods: ['PATCH'])]
    public function editProfile(Request $request)
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (isset($data['login'])) {
            $user->setLogin($data['login']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'user' => '$user'
        ]);
    }
}
