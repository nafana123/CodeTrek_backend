<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\ReplyToMessage;
use App\Entity\Task;
use App\Repository\DiscussionRepository;
use App\Repository\ReplyToMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DiscussionController extends AbstractController
{
    private DiscussionRepository $discussionRepository;
    private EntityManagerInterface $entityManager;
    private ReplyToMessageRepository $replyToMessageRepository;

    public function __construct(DiscussionRepository $discussionRepository, EntityManagerInterface $entityManager, ReplyToMessageRepository $replyToMessageRepository)
    {
        $this->discussionRepository = $discussionRepository;

        $this->entityManager = $entityManager;

        $this->replyToMessageRepository = $replyToMessageRepository;
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
                    'id' => $reply->getId(),
                    'user' => $this->formatUser($reply->getUser(), $currentUser),
                    'replyToMessage' => $reply->getReplyTo(),
                    'isCurrentUser' => $currentUser->getId() === $reply->getUser()->getId(),
                    'createdAt' => $discussion->getData()

                ];
            }

            $data[] = [
                'id' => $discussion->getId(),
                'user' => $this->formatUser($discussion->getUser(), $currentUser),
                'message' => $discussion->getMessage(),
                'isCurrentUser' => $currentUser->getId() === $discussion->getUser()->getId(),
                'replies' => $replyData,
                'createdAt' => $discussion->getData()
            ];
        }

        return $this->json($data);
    }

    private function formatUser($user, $currentUser)
    {
        return [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'avatar' => $user->getAvatar(),
            'isCurrentUser' => $user->getId() === $currentUser->getId(),
        ];
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
    #[Route('/api/edit/discussion/{id}', name: 'edit_discussion', methods: ['PUT'])]
    public function editDiscussion(Discussion $discussion, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $discussion->setMessage($data['message']);
        $this->entityManager->flush();

        return $this->json(['status' => 'ok']);
    }
    #[Route('/api/delete/discussion/{id}', name: 'delete_discussion', methods: ['DELETE'])]
    public function deleteDiscussion(Discussion $discussion): JsonResponse
    {
        $user = $this->getUser();
        if ($discussion->getUser() !== $user) {
            return $this->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $this->entityManager->remove($discussion);
        $this->entityManager->flush();

        return $this->json(['status' => 'deleted']);
    }
    #[Route('/api/reply/{replyId}', name: 'edit_reply', methods: ['PUT'])]
    public function editReply($replyId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newMessage = $data['message'];

        $reply = $this->replyToMessageRepository->find($replyId);

        $reply->setReplyTo($newMessage);
        $this->entityManager->flush();

        return $this->json(['status' => 'success', 'message' => 'Reply updated']);
    }
    #[Route('/api/delete/reply/{replyId}', name: 'delete_reply', methods: ['DELETE'])]
    public function deleteReply($replyId): JsonResponse
    {
        $reply = $this->replyToMessageRepository->find($replyId);

        $this->entityManager->remove($reply);
        $this->entityManager->flush();

        return $this->json(['status' => 'success', 'message' => 'Reply deleted']);
    }
}