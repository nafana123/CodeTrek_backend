<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\ReplyToMessage;
use App\Entity\Task;
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
                'avatar' => $user->getAvatar(),
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

        $data = [];
        foreach ($favoriteTasks as $task) {
            $data[] = [
                'id' => $task->getTask()->getTaskId(),
                'title' => $task->getTask()->getTitle(),
                'difficulty' => $task->getTask()->getDifficulty()->getLevel(),
            ];
        }

        return $this->json($data);
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

    #[Route('/api/user/discussion', name: 'user_discussion', methods: ['GET'])]
    public function getUserDiscussion(): Response
    {
        $user = $this->getUser();

        $userDiscussions = $this->entityManager->getRepository(Discussion::class)->findBy(['user' => $user]);

        $data = [];

        foreach ($userDiscussions as $discussion) {
            $replies = $this->entityManager->getRepository(ReplyToMessage::class)->findBy(['discussion' => $discussion]);

            $data[] = [
                'taskId' => $discussion->getTask()->getTaskId(),
                'taskTitle' => $discussion->getTask()->getTitle(),
                'difficulty' => $discussion->getTask()->getDifficulty()->getLevel(),
                'message' => $discussion->getMessage(),
                'createdAt' => $discussion->getData(),
                'replies' => array_map(function ($reply) {
                    return [
                        'replyMessage' => $reply->getReplyTo(),
                        'createdAt' => $reply->getData(),
                        'user' => $reply->getUser()->getLogin(),
                    ];
                }, $replies)
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/user/avatar', name: 'upload_avatar', methods: ['POST'])]
    public function uploadAvatar(Request $request): Response
    {
        $user = $this->getUser();
        $file = $request->files->get('avatar');

        if (!$file) {
            return $this->json(['error' => 'Файл не найден'], Response::HTTP_BAD_REQUEST);
        }

        $uploadsDir = $this->getParameter('avatars_directory');

        if ($user->getAvatar()) {
            $oldAvatarPath = $this->getParameter('kernel.project_dir') . '/public' . $user->getAvatar();
            if (file_exists($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        $filename = 'avatar_user_' . $user->getId() . '.' . $file->guessExtension();
        $file->move($uploadsDir, $filename);

        $user->setAvatar('/uploads/avatars/' . $filename);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['avatar' => $user->getAvatar()]);
    }

    #[Route('/api/delete/user/avatar', name: 'delete_avatar', methods: ['POST'])]
    public function deleteAvatar(): Response
    {
        $user = $this->getUser();

        if ($user->getAvatar()) {
            $avatarFullPath = $this->getParameter('kernel.project_dir') . '/public' . $user->getAvatar();

            if (file_exists($avatarFullPath)) {
                unlink($avatarFullPath);
            }

            $user->setAvatar(null);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(['success' => true], Response::HTTP_OK);
        }

        return $this->json(['error' => 'No avatar to delete'], Response::HTTP_BAD_REQUEST);
    }


}
