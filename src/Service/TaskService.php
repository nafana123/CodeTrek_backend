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
    public function getFormattedInput($language, $testCase, $code, $executionTemplate): array
    {
        if ($language === 'c++' || $language === 'c#' || $language === 'go') {
            $formattedInput = str_replace(['[', ']'], ['{', '}'], json_encode($testCase));
        } else if ($language === 'java') {
            $formattedInput = str_replace(['[', ']'], ['{', '}'], json_encode($testCase));
            $executionCode = sprintf($executionTemplate, $formattedInput);
            $code = preg_replace('/public class Main \{/', $executionCode, $code, 1);
        } else {
            $formattedInput = json_encode($testCase);
        }

        return [$formattedInput, $code];
    }
    public function prepareUserCode(string $language, $input, string $code, string $executionTemplate, string $outputFunction): string
    {
        [$formattedInput, $formattedCode] = $this->getFormattedInput($language, $input, $code, $executionTemplate);

        $outputCode = sprintf($outputFunction, $formattedInput);

        $userCode = $formattedCode . ($language !== 'java' ? "\n" . $outputCode : '');

        return $userCode;
    }
}