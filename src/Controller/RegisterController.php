<?php
namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route("/api/register", name: "register", methods: ["POST", "OPTIONS"])]
    public function register(Request $request): Response
    {
        return $this->json(['status' => 'User registered successfully!']);
    }

}