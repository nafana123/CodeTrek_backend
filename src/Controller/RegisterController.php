<?php
namespace App\Controller;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\RequestStack;


class RegisterController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    #[Route("/api/register", name: "register", methods: ["POST", "OPTIONS"])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setLogin($data['login']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $registrationDate = new \DateTime();
        $user->setData($registrationDate->format('d-m-Y'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return new Response(json_encode(['message' => 'Registration successful']), Response::HTTP_CREATED);
    }
}