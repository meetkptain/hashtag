<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gamification\PointsService;
use App\Services\Gamification\BadgeService;
use App\Models\UserPoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GamificationController extends Controller
{
    protected PointsService $pointsService;
    protected BadgeService $badgeService;

    public function __construct(PointsService $pointsService, BadgeService $badgeService)
    {
        $this->pointsService = $pointsService;
        $this->badgeService = $badgeService;
    }

    /**
     * GET /api/gamification/user
     * 
     * Données gamification d'un utilisateur
     */
    public function getUser(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'platform' => 'required|in:instagram,facebook,twitter,google'
        ]);

        $userPoint = $this->pointsService->getUserPoints(
            $request->input('username'),
            $request->input('platform')
        );

        if (!$userPoint) {
            return response()->json([
                'exists' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'exists' => true,
            'user' => [
                'username' => $userPoint->user_identifier,
                'platform' => $userPoint->platform,
                'total_points' => $userPoint->total_points,
                'weekly_points' => $userPoint->weekly_points,
                'monthly_points' => $userPoint->monthly_points,
                'streak_days' => $userPoint->streak_days,
                'rank' => $userPoint->rank,
                'weekly_rank' => $userPoint->weekly_rank,
                'badge_count' => $userPoint->badge_count,
                'last_post_at' => $userPoint->last_post_at
            ]
        ]);
    }

    /**
     * GET /api/gamification/user/badges
     * 
     * Badges d'un utilisateur
     */
    public function getUserBadges(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'platform' => 'required|in:instagram,facebook,twitter,google'
        ]);

        $userPoint = $this->pointsService->getUserPoints(
            $request->input('username'),
            $request->input('platform')
        );

        if (!$userPoint) {
            return response()->json(['badges' => []]);
        }

        $badges = $this->badgeService->getUserBadges($userPoint);

        return response()->json([
            'badges' => $badges,
            'count' => $badges->count()
        ]);
    }

    /**
     * GET /api/gamification/user/progress
     * 
     * Progression vers badges
     */
    public function getUserProgress(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'platform' => 'required|in:instagram,facebook,twitter,google'
        ]);

        $userPoint = $this->pointsService->getUserPoints(
            $request->input('username'),
            $request->input('platform')
        );

        if (!$userPoint) {
            return response()->json(['progress' => []]);
        }

        $progress = $this->badgeService->getUserProgress($userPoint);

        return response()->json([
            'progress' => $progress,
            'total_badges' => count($progress),
            'unlocked_count' => collect($progress)->where('unlocked', true)->count()
        ]);
    }

    /**
     * POST /api/gamification/badge/mark-viewed
     * 
     * Marquer un badge comme vu
     */
    public function markBadgeViewed(Request $request): JsonResponse
    {
        $request->validate([
            'user_badge_id' => 'required|integer|exists:user_badges,id'
        ]);

        $success = $this->badgeService->markAsViewed($request->input('user_badge_id'));

        return response()->json([
            'success' => $success
        ]);
    }

    /**
     * GET /api/gamification/stats
     * 
     * Stats globales gamification
     */
    public function stats(): JsonResponse
    {
        $pointsStats = $this->pointsService->getStats();
        $badgeStats = $this->badgeService->getStats();

        return response()->json([
            'points' => $pointsStats,
            'badges' => $badgeStats
        ]);
    }
}

