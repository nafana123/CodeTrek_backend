<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\ReplyToMessage;
use App\Entity\Task;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DiscussionController extends AbstractController
{
    private DiscussionRepository $discussionRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(DiscussionRepository $discussionRepository, EntityManagerInterface $entityManager)
    {
        $this->discussionRepository = $discussionRepository;

        $this->entityManager = $entityManager;
    }

    #[Route('/api/discussion/{id}', name: 'discussion', methods: ['GET'])]
    public function discussion($id, Request $request): JsonResponse
    {
        $taskDiscussions = $this->discussionRepository->findBy(['task' => $id]);
        $data = [];

        $currentUser = $this->getUser();

        foreach ($taskDiscussions as $discussion) {
            $replies = $this->entityManager->getRepository(ReplyToMessage::class)->findBy(['discussion' => $discussion]);

            $replyData = [];
            foreach ($replies as $reply) {
                $replyData[] = [
                    'user' => $reply->getUser(),
                    'replyToMessage' => $reply->getReplyTo(),
                    'isCurrentUser' => $currentUser->getId() === $reply->getUser()->getId(),
                    'createdAt' => $discussion->getData()

                ];
            }

            $data[] = [
                'id' => $discussion->getId(),
                'user' => $discussion->getUser(),
                'message' => $discussion->getMessage(),
                'isCurrentUser' => $currentUser->getId() === $discussion->getUser()->getId(),
                'replies' => $replyData,
                'createdAt' => $discussion->getData()
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/add/discussion/{id}', name: 'add_discussion', methods: ['POST'])]
    public function addDiscussion(Task $task, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $message = $data['message'];

        $discussion = new Discussion();
        $discussion->setTask($task);
        $discussion->setUser($user);
        $discussion->setMessage($message);
        $discussion->setData(new \DateTime());

        $this->entityManager->persist($discussion);
        $this->entityManager->flush();

        return $this->json(['id' => $discussion->getId()]);
    }

    #[Route('/api/reply/{discussionId}', name: 'add_reply', methods: ['POST'])]
    public function addReplyToMessage($discussionId, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $replyMessage = $data['message'];

        $discussion = $this->discussionRepository->find($discussionId);
        if (!$discussion) {
            return $this->json(['status' => 'error', 'message' => 'Discussion not found'], 404);
        }

        $reply = new ReplyToMessage();
        $reply->setDiscussion($discussion);
        $reply->setUser($user);
        $reply->setReplyTo($replyMessage);
        $reply->setData(new \DateTime());

        $this->entityManager->persist($reply);
        $this->entityManager->flush();

        return $this->json(['id' => $reply->getId()]);
    }
}