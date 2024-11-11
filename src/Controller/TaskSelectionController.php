<?php
namespace App\Controller;

use App\Entity\Language;
use App\Entity\UserLanguage;
use App\Service\TaskService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class TaskSelectionController extends AbstractController
{
    private $userService;
    private $entityManager;

    private $taskService;
    public function __construct(UserService $userService, EntityManagerInterface $entityManager, TaskService $taskService)
    {
         $this->userService = $userService;

         $this->entityManager = $entityManager;

         $this->taskService = $taskService;
    }
    #[Route("api/language/selection", name: "language_selection", methods: ["POST"])]
    public function languageSelection(Request $request)
    {
        $user = $this->userService->getUserByToken($request);

        $data = json_decode($request->getContent(), true);
        $lang = $this->entityManager->getRepository(Language::class)->findOneBy(['name' => $data['lang']]);
        $activeLanguage = $this->entityManager->getRepository(UserLanguage::class)->findOneBy([
            'user' => $user,
            'language' => $lang
        ]);
        if($activeLanguage){
            $this->entityManager->remove($activeLanguage);
        }
        else{
            $activeLanguage = new UserLanguage();
            $activeLanguage->setLanguage($lang);
            $activeLanguage->setUser($user);
            $this->entityManager->persist($activeLanguage);
        }

        $this->entityManager->flush();

        $activeLanguages = $this->taskService->getUserLanguages($user);

        return $this->json(['lang' => $activeLanguages]);
    }
    #[Route("api/user/languages", name: "user_languages", methods: ["GET"])]
    public function userLanguages(Request $request)
    {
        $user = $this->userService->getUserByToken($request);
        $activeLanguages = $this->taskService->getUserLanguages($user);

        return $this->json(['lang' => $activeLanguages]);

    }
}

