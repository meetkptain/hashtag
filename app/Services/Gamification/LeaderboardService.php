<?php

namespace App\Services\Gamification;

use App\Models\UserPoint;
use App\Models\Leaderboard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class LeaderboardService
{
    /**
     * Obtenir leaderboard global (all-time)
     */
    public function getGlobalLeaderboard(int $limit = 100): Collection
    {
        return Cache::remember('leaderboard_global', 60, function() use ($limit) {
            return UserPoint::orderBy('total_points', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($userPoint, $index) {
                    return [
                        'rank' => $index + 1,
                        'user_identifier' => $userPoint->user_identifier,
                        'platform' => $userPoint->platform,
                        'points' => $userPoint->total_points,
                        'badge_count' => $userPoint->badges()->count(),
                        'streak_days' => $userPoint->streak_days
                    ];
                });
        });
    }

    /**
     * Obtenir leaderboard hebdomadaire
     */
    public function getWeeklyLeaderboard(int $limit = 100): Collection
    {
        return Cache::remember('leaderboard_weekly', 60, function() use ($limit) {
            return UserPoint::orderBy('weekly_points', 'desc')
                ->where('weekly_points', '>', 0)
                ->limit($limit)
                ->get()
                ->map(function($userPoint, $index) {
                    return [
                        'rank' => $index + 1,
                        'user_identifier' => $userPoint->user_identifier,
                        'platform' => $userPoint->platform,
                        'points' => $userPoint->weekly_points,
                        'total_points' => $userPoint->total_points,
                        'badge_count' => $userPoint->badges()->count()
                    ];
                });
        });
    }

    /**
     * Obtenir leaderboard mensuel
     */
    public function getMonthlyLeaderboard(int $limit = 100): Collection
    {
        return Cache::remember('leaderboard_monthly', 60, function() use ($limit) {
            return UserPoint::orderBy('monthly_points', 'desc')
                ->where('monthly_points', '>', 0)
                ->limit($limit)
                ->get()
                ->map(function($userPoint, $index) {
                    return [
                        'rank' => $index + 1,
                        'user_identifier' => $userPoint->user_identifier,
                        'platform' => $userPoint->platform,
                        'points' => $userPoint->monthly_points,
                        'total_points' => $userPoint->total_points,
                        'badge_count' => $userPoint->badges()->count()
                    ];
                });
        });
    }

    /**
     * Obtenir position d'un utilisateur
     */
    public function getUserPosition(string $username, string $platform, string $type = 'global'): array
    {
        $userPoint = UserPoint::where('user_identifier', $username)
            ->where('platform', $platform)
            ->first();

        if (!$userPoint) {
            return [
                'rank' => null,
                'points' => 0,
                'total_users' => 0,
                'percentile' => 0
            ];
        }

        $column = match($type) {
            'weekly' => 'weekly_points',
            'monthly' => 'monthly_points',
            default => 'total_points'
        };

        $points = $userPoint->$column;
        $rank = UserPoint::where($column, '>', $points)->count() + 1;
        $totalUsers = UserPoint::where($column, '>', 0)->count();

        return [
            'rank' => $rank,
            'points' => $points,
            'total_users' => $totalUsers,
            'percentile' => $totalUsers > 0 ? round((1 - ($rank / $totalUsers)) * 100, 1) : 0
        ];
    }

    /**
     * Invalider cache leaderboard
     */
    public function invalidateCache(): void
    {
        Cache::tags(['leaderboard'])->flush();
        
        Cache::forget('leaderboard_global');
        Cache::forget('leaderboard_weekly');
        Cache::forget('leaderboard_monthly');
    }

    /**
     * Obtenir stats leaderboard
     */
    public function getStats(): array
    {
        return Cache::remember('leaderboard_stats', 300, function() {
            return [
                'total_users' => UserPoint::count(),
                'active_users_week' => UserPoint::where('weekly_points', '>', 0)->count(),
                'active_users_month' => UserPoint::where('monthly_points', '>', 0)->count(),
                'total_points_distributed' => UserPoint::sum('total_points'),
                'average_points_per_user' => round(UserPoint::avg('total_points'), 2),
                'top_user' => UserPoint::orderBy('total_points', 'desc')->first(),
                'top_user_this_week' => UserPoint::orderBy('weekly_points', 'desc')->first(),
                'top_user_this_month' => UserPoint::orderBy('monthly_points', 'desc')->first()
            ];
        });
    }

    /**
     * Sauvegarder snapshot leaderboard (optionnel, pour historique)
     */
    public function saveSnapshot(string $type, string $period): int
    {
        $column = match($type) {
            'weekly' => 'weekly_points',
            'monthly' => 'monthly_points',
            default => 'total_points'
        };

        $topUsers = UserPoint::orderBy($column, 'desc')
            ->where($column, '>', 0)
            ->limit(100)
            ->get();

        $saved = 0;
        foreach ($topUsers as $index => $userPoint) {
            try {
                Leaderboard::create([
                    'type' => $type,
                    'period' => $period,
                    'user_point_id' => $userPoint->id,
                    'rank' => $index + 1,
                    'points' => $userPoint->$column
                ]);
                $saved++;
            } catch (\Exception $e) {
                // Peut déjà exister (unique constraint)
                continue;
            }
        }

        return $saved;
    }
}

