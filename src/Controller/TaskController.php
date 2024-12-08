<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
    public function executeCode(string $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        $tempFilePath = sys_get_temp_dir() . '/user_code_' . uniqid() . '.js';
        file_put_contents($tempFilePath, $code);

        $command = escapeshellcmd("node $tempFilePath");

        $descriptorspec = [
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            if ($error) {
                unlink($tempFilePath);
                return $this->json([
                    'success' => false,
                    'error' => 'Синтаксическая ошибка: ' . $error,
                ]);
            }

            unlink($tempFilePath);
            return $this->json([
                'success' => true,
                'output' => $output,
            ]);
        }

        unlink($tempFilePath);
        return $this->json([
            'success' => false,
            'error' => 'Ошибка выполнения кода. Попробуйте позже.',
        ]);
    }
}
