<?php
namespace App\Controller;

use App\Entity\Language;
use App\Entity\UserLanguage;
use App\Service\UserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskSelectionController extends AbstractController
{
    private $userService;

    private $entityManager;
    public function __construct(UserService $userService, EntityManagerInterface $entityManager)
    {
         $this->userService = $userService;

         $this->entityManager = $entityManager;
    }
    #[Route("api/language/selection", name: "language_selection", methods: ["POST"])]
    public function languageSelection(Request $request)
    {
        $user = $this->userService->getUserByToken($request);

        $data = json_decode($request->getContent(), true);
        $lang = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $data['lang']]);

        $activeLanguage = new UserLanguage();
        $activeLanguage->setLanguage($lang);
        $activeLanguage->setUser($user);
        $this->entityManager->persist($activeLanguage);
        $this->entityManager->flush();


        return $this->json([
            'token' => $user->getId(),
            'lang' => $lang->getId(),
        ]);
    }
}

