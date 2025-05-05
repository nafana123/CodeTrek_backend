<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Cache\CacheItemPoolInterface;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;
    private SendMailService $sendMailService;
    private CacheItemPoolInterface $cache;

    public function __construct(
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        SendMailService $sendMailService,
        CacheItemPoolInterface $cache
    ) {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->sendMailService = $sendMailService;
        $this->cache = $cache;
    }

    #[Route("/api/register", name: "register", methods: ["POST", "OPTIONS"])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $cacheKey = 'confirmation_code_' . md5(trim(strtolower($data['email'])));
        $cachedItem = $this->cache->getItem($cacheKey);
        $cachedCode = (string) $cachedItem->get();

        if ((string) $data['codeToSend'] !== $cachedCode) {
            return new Response('Неверный код подтверждения', Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setData((new \DateTime())->format('d-m-Y'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->jwtManager->create($user);
        $this->cache->deleteItem($cacheKey);

        return new Response(
            json_encode([
                'message' => 'Регистрация прошла успешно',
                'token' => $token
            ]),
            Response::HTTP_CREATED
        );
    }
    #[Route("/api/confirm", name: "confirm", methods: ["POST"])]
    public function confirm(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = random_int(10000, 99999);

        $cacheKey = 'confirmation_code_' . md5($data['email']);
        $cacheItem = $this->cache->getItem($cacheKey);
        $cacheItem->set($code);
        $cacheItem->expiresAfter(300);
        $this->cache->save($cacheItem);

        $this->sendMailService->sendMail($data['email'], $code);

        return $this->json('success', Response::HTTP_CREATED);
    }
    #[Route("/api/check-email", name: "check_email", methods: ["POST"])]
    public function checkEmail(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $existingUser = $this->entityManager->getRepository(User::class)->findOneByEmail($data['email']);

        return $this->json(['exists' => (bool)$existingUser]);
    }
}