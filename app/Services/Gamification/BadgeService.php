<?php

namespace App\Services\Gamification;

use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\UserPoint;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * Vérifier critères badges pour un utilisateur
     * 
     * @param UserPoint $userPoint
     * @return Collection Nouveaux badges débloqués
     */
    public function checkBadgeCriteria(UserPoint $userPoint): Collection
    {
        $badges = Badge::active()->get();
        $newBadges = collect();

        foreach ($badges as $badge) {
            // Vérifier si badge déjà obtenu
            if ($this->hasBadge($userPoint, $badge)) {
                continue;
            }

            // Vérifier critères
            if ($this->meetsCriteria($userPoint, $badge)) {
                $userBadge = $this->unlockBadge($userPoint, $badge);
                $newBadges->push($userBadge);
            }
        }

        return $newBadges;
    }

    /**
     * Débloquer un badge
     */
    public function unlockBadge(UserPoint $userPoint, Badge $badge): UserBadge
    {
        $userBadge = UserBadge::create([
            'user_point_id' => $userPoint->id,
            'badge_id' => $badge->id,
            'unlocked_at' => now()
        ]);

        // Dispatcher event
        event(new \App\Events\BadgeUnlocked($userBadge, $userPoint, $badge));

        Log::info("Badge unlocked", [
            'user' => $userPoint->user_identifier,
            'badge' => $badge->key
        ]);

        return $userBadge;
    }

    /**
     * Vérifier si badge déjà obtenu
     */
    protected function hasBadge(UserPoint $userPoint, Badge $badge): bool
    {
        return UserBadge::where('user_point_id', $userPoint->id)
            ->where('badge_id', $badge->id)
            ->exists();
    }

    /**
     * Vérifier si critères remplis
     */
    protected function meetsCriteria(UserPoint $userPoint, Badge $badge): bool
    {
        $criteria = $badge->criteria;

        if (!isset($criteria['type'])) {
            return false;
        }

        return match($criteria['type']) {
            'posts_count' => $this->checkPostsCount($userPoint, $criteria),
            'post_likes' => $this->checkPostLikes($userPoint, $criteria),
            'streak' => $this->checkStreak($userPoint, $criteria),
            'leaderboard' => $this->checkLeaderboardRank($userPoint, $criteria),
            'post_number' => $this->checkPostNumber($userPoint, $criteria),
            'post_time' => $this->checkPostTime($userPoint, $criteria),
            'posts_speed' => $this->checkPostsSpeed($userPoint, $criteria),
            default => false
        };
    }

    /**
     * Vérifier nombre de posts
     */
    protected function checkPostsCount(UserPoint $userPoint, array $criteria): bool
    {
        $minPosts = $criteria['min_posts'];
        
        $query = Post::where('author_username', $userPoint->user_identifier)
            ->where('platform', $userPoint->platform);

        // Filtres optionnels
        if (isset($criteria['hashtags'])) {
            $query->where(function($q) use ($criteria) {
                foreach ($criteria['hashtags'] as $hashtag) {
                    $q->orWhere('content', 'like', "%{$hashtag}%");
                }
            });
        }

        if (isset($criteria['date_range'])) {
            $query->whereBetween('created_at', [
                $criteria['date_range']['start'],
                $criteria['date_range']['end']
            ]);
        }

        $postCount = $query->count();

        return $postCount >= $minPosts;
    }

    /**
     * Vérifier likes posts
     */
    protected function checkPostLikes(UserPoint $userPoint, array $criteria): bool
    {
        $minLikes = $criteria['min_likes'];
        $minPosts = $criteria['min_posts'];

        $postsWithMinLikes = Post::where('author_username', $userPoint->user_identifier)
            ->where('platform', $userPoint->platform)
            ->where('likes_count', '>=', $minLikes)
            ->count();

        return $postsWithMinLikes >= $minPosts;
    }

    /**
     * Vérifier streak
     */
    protected function checkStreak(UserPoint $userPoint, array $criteria): bool
    {
        $minDays = $criteria['min_days'];
        return $userPoint->streak_days >= $minDays;
    }

    /**
     * Vérifier rang leaderboard
     */
    protected function checkLeaderboardRank(UserPoint $userPoint, array $criteria): bool
    {
        $targetRank = $criteria['rank'];
        $period = $criteria['period'] ?? 'monthly'; // 'monthly', 'weekly'

        $column = $period === 'weekly' ? 'weekly_points' : 'monthly_points';
        $points = $userPoint->$column;

        $rank = UserPoint::where($column, '>', $points)->count() + 1;

        return $rank <= $targetRank;
    }

    /**
     * Vérifier numéro post (ex: post #7777)
     */
    protected function checkPostNumber(UserPoint $userPoint, array $criteria): bool
    {
        $targetNumber = $criteria['target_number'];
        
        // Compter posts tenant
        $totalPosts = Post::count();

        return $totalPosts == $targetNumber;
    }

    /**
     * Vérifier heure post (ex: 11:11)
     */
    protected function checkPostTime(UserPoint $userPoint, array $criteria): bool
    {
        $targetTime = $criteria['target_time']; // '11:11'

        $hasPostAtTime = Post::where('author_username', $userPoint->user_identifier)
            ->where('platform', $userPoint->platform)
            ->whereRaw("TIME(created_at) = ?", [$targetTime . ':00'])
            ->exists();

        return $hasPostAtTime;
    }

    /**
     * Vérifier vitesse posts (ex: 10 posts en 1h)
     */
    protected function checkPostsSpeed(UserPoint $userPoint, array $criteria): bool
    {
        $minPosts = $criteria['min_posts'];
        $timeframeHours = $criteria['timeframe_hours'];

        // Trouver fenêtre temporelle avec X posts
        $posts = Post::where('author_username', $userPoint->user_identifier)
            ->where('platform', $userPoint->platform)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($posts->count() < $minPosts) {
            return false;
        }

        // Sliding window
        for ($i = 0; $i <= $posts->count() - $minPosts; $i++) {
            $firstPost = $posts[$i];
            $lastPost = $posts[$i + $minPosts - 1];

            $hoursDiff = $firstPost->created_at->diffInHours($lastPost->created_at);

            if ($hoursDiff <= $timeframeHours) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir badges utilisateur
     */
    public function getUserBadges(UserPoint $userPoint): Collection
    {
        return UserBadge::where('user_point_id', $userPoint->id)
            ->with('badge')
            ->orderBy('unlocked_at', 'desc')
            ->get();
    }

    /**
     * Obtenir progression badges utilisateur
     */
    public function getUserProgress(UserPoint $userPoint): array
    {
        $allBadges = Badge::active()
            ->notSecret() // Pas les secrets
            ->orderBy('category')
            ->orderBy('display_order')
            ->get();

        $userBadgeIds = UserBadge::where('user_point_id', $userPoint->id)
            ->pluck('badge_id')
            ->toArray();

        return $allBadges->map(function($badge) use ($userBadgeIds, $userPoint) {
            $unlocked = in_array($badge->id, $userBadgeIds);
            $progress = $unlocked ? 100 : $this->calculateProgress($userPoint, $badge);

            return [
                'badge' => $badge,
                'unlocked' => $unlocked,
                'progress' => $progress
            ];
        })->toArray();
    }

    /**
     * Calculer progression vers badge
     */
    protected function calculateProgress(UserPoint $userPoint, Badge $badge): int
    {
        $criteria = $badge->criteria;

        if ($criteria['type'] === 'posts_count') {
            $postCount = Post::where('author_username', $userPoint->user_identifier)
                ->where('platform', $userPoint->platform)
                ->count();
            
            $required = $criteria['min_posts'];
            return min(100, round(($postCount / $required) * 100));
        }

        if ($criteria['type'] === 'streak') {
            $required = $criteria['min_days'];
            return min(100, round(($userPoint->streak_days / $required) * 100));
        }

        if ($criteria['type'] === 'post_likes') {
            $postsWithLikes = Post::where('author_username', $userPoint->user_identifier)
                ->where('platform', $userPoint->platform)
                ->where('likes_count', '>=', $criteria['min_likes'])
                ->count();
            
            $required = $criteria['min_posts'];
            return min(100, round(($postsWithLikes / $required) * 100));
        }

        // Par défaut
        return 0;
    }

    /**
     * Marquer badge comme vu
     */
    public function markAsViewed(int $userBadgeId): bool
    {
        return UserBadge::where('id', $userBadgeId)
            ->update(['viewed_at' => now()]);
    }

    /**
     * Obtenir stats badges
     */
    public function getStats(): array
    {
        return Cache::remember('badge_stats', 300, function() {
            return [
                'total_badges' => Badge::active()->count(),
                'total_unlocks' => UserBadge::count(),
                'users_with_badges' => UserBadge::distinct('user_point_id')->count(),
                'most_common_badge' => UserBadge::select('badge_id', DB::raw('count(*) as count'))
                    ->groupBy('badge_id')
                    ->orderBy('count', 'desc')
                    ->with('badge')
                    ->first(),
                'rarest_badge' => UserBadge::select('badge_id', DB::raw('count(*) as count'))
                    ->groupBy('badge_id')
                    ->orderBy('count', 'asc')
                    ->with('badge')
                    ->first()
            ];
        });
    }

    /**
     * Obtenir badges non vus pour un utilisateur
     */
    public function getUnviewedBadges(UserPoint $userPoint): Collection
    {
        return UserBadge::where('user_point_id', $userPoint->id)
            ->unviewed()
            ->with('badge')
            ->get();
    }
}

