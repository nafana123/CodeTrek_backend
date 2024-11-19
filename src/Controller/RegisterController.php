<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $entityManager;
    private $jwtManager;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
    }

    #[Route("/api/register", name: "register", methods: ["POST", "OPTIONS"])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $existingUser = $this->entityManager->getRepository(User::class)->findOneByEmail($data['email']);
        if ($existingUser) {
            return new Response('Пользователь с таким email уже существует', Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));

        $registrationDate = new \DateTime();
        $user->setData($registrationDate->format('d-m-Y'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->jwtManager->create($user);

        return new Response(
            json_encode([
                'message' => 'Регистрация прошла успешно',
                'token' => $token
            ]),
            Response::HTTP_CREATED
        );
    }
}