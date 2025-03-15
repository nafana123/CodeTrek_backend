<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\LanguageRepository;
use App\Repository\SolvedTaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    private UserRepository $userRepository;
    private SolvedTaskRepository $solvedTaskRepository;
    private LanguageRepository $languageRepository;
    public function __construct(UserRepository $userRepository, SolvedTaskRepository $solvedTaskRepository, LanguageRepository $languageRepository)
    {
        $this->userRepository = $userRepository;
        $this->solvedTaskRepository = $solvedTaskRepository;
        $this->languageRepository = $languageRepository;
    }
    #[Route('/api/admin/user/all', name: 'all_users')]
    public function getAllUser(){
        $allUsers = $this->userRepository->findAll();
        return $this->json($allUsers);
    }

    #[Route('/api/admin/task/statistics', name: 'task_statistics')]
    public function taskStatistics()
    {
        $solvedTask = $this->solvedTaskRepository->findAll();

        $languages = $this->languageRepository->findAll();

        $data = [];
        foreach ($languages as $language) {
            $data[$language->getName()] = 0;
        }

        foreach ($solvedTask as $task) {
            $taskLanguage = $task->getTaskLanguage()->getLanguage()->getName();
            if (isset($data[$taskLanguage])) {
                $data[$taskLanguage]++;
            }
        }

        return $this->json($data);
    }

    #[Route('/api/admin/user/{id}/info', name: 'user_info')]
    public function userinfo(User $user)
    {
        $taskUser = $this->solvedTaskRepository->findBy(['user' => $user]);

        $response = [
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'email' => $user->getEmail(),
                'data' => $user->getData(),
            ],
            'tasks' => []
        ];

        foreach ($taskUser as $task) {
            $detailsTask = $task->getTaskLanguage()->getTask();

            $response['tasks'][] = [
                'task' => $detailsTask,
                'solvedTask' => $task->getCode(),
                'language' => $task->getTaskLanguage()->getLanguage()->getName(),
            ];
        }

        return $this->json($response);
    }
}