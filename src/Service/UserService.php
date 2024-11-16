<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserService
{
    private $JWTManager;
    private $entityManager;

    public function __construct(JWTTokenManagerInterface $JWTManager, EntityManagerInterface $entityManager)
    {
        $this->JWTManager = $JWTManager;
        $this->entityManager = $entityManager;
    }

}