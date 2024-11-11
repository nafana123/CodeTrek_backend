<?php

namespace App\Service;

use App\Entity\UserLanguage;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function getUserLanguages($user): array
    {
        $activeLanguage = $this->entityManager->getRepository(UserLanguage::class)->findBy(['user' => $user]);

        $languages = [];
        foreach ($activeLanguage as $language){
            $languages[] = [
                'name' => $language->getLanguage()->getName(),
            ];
        }

        return $languages;
    }
}