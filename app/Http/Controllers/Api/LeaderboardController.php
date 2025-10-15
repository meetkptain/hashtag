<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gamification\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * GET /api/leaderboard/global
     * 
     * Classement all-time (jamais reset)
     */
    public function global(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $leaderboard = $this->leaderboardService->getGlobalLeaderboard($limit);

        return response()->json([
            'leaderboard' => $leaderboard,
            'type' => 'global',
            'count' => $leaderboard->count()
        ]);
    }

    /**
     * GET /api/leaderboard/weekly
     * 
     * Classement hebdomadaire (reset dimanche 00:00)
     */
    public function weekly(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $leaderboard = $this->leaderboardService->getWeeklyLeaderboard($limit);

        return response()->json([
            'leaderboard' => $leaderboard,
            'type' => 'weekly',
            'count' => $leaderboard->count(),
            'resets_at' => now()->endOfWeek()->toIso8601String()
        ]);
    }

    /**
     * GET /api/leaderboard/monthly
     * 
     * Classement mensuel (reset 1er du mois)
     */
    public function monthly(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $leaderboard = $this->leaderboardService->getMonthlyLeaderboard($limit);

        return response()->json([
            'leaderboard' => $leaderboard,
            'type' => 'monthly',
            'count' => $leaderboard->count(),
            'resets_at' => now()->endOfMonth()->toIso8601String()
        ]);
    }

    /**
     * GET /api/leaderboard/position
     * 
     * Position d'un utilisateur spécifique
     */
    public function position(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'platform' => 'required|in:instagram,facebook,twitter,google',
            'type' => 'sometimes|in:global,weekly,monthly'
        ]);

        $position = $this->leaderboardService->getUserPosition(
            $request->input('username'),
            $request->input('platform'),
            $request->input('type', 'global')
        );

        return response()->json($position);
    }

    /**
     * GET /api/leaderboard/stats
     * 
     * Statistiques globales leaderboard
     */
    public function stats(): JsonResponse
    {
        $stats = $this->leaderboardService->getStats();

        return response()->json($stats);
    }
}

