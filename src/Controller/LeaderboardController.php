<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\LeaderboardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeaderboardController extends AbstractController
{
    private LeaderboardRepository $leaderboardRepository;
    public function __construct(LeaderboardRepository $leaderboardRepository)
    {
        $this->leaderboardRepository = $leaderboardRepository;
    }

    #[Route('api/leaderboard', name: 'leaderboard', methods: ['GET'])]
    public function leaderboard()
    {
        $leaders = $this->leaderboardRepository->findBy([], ['points' => 'DESC']);
        return $this->json($leaders);
    }
}
