<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class LoginController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/api/login", name: "login", methods: ["POST", "OPTIONS"])]
    public function login(Request $request, JWTTokenManagerInterface $JWTManager)
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user || !password_verify($password, $user->getPassword())) {
            return $this->json(['error' => 'Неверный логин или пароль.'], Response::HTTP_UNAUTHORIZED);
        }
        return $this->json(['token' => $JWTManager->create($user)]);
    }
}