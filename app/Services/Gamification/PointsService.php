<?php

namespace App\Services\Gamification;

use App\Models\UserPoint;
use App\Models\PointTransaction;
use App\Models\Post;
use App\Models\Contest;
use App\Models\GamificationConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PointsService
{
    /**
     * Attribuer des points pour un post
     * 
     * @param Post $post
     * @return int Points attribués
     */
    public function awardPointsForPost(Post $post): int
    {
        // 1. Obtenir ou créer utilisateur AUTOMATIQUEMENT ✨
        $userPoint = $this->getOrCreateUserPoint(
            $post->author_username,
            $post->platform
        );

        if (!$userPoint) {
            Log::error("Failed to get/create user point", [
                'username' => $post->author_username,
                'platform' => $post->platform
            ]);
            return 0;
        }

        // 2. Vérifier rate limiting (anti-spam)
        if ($this->isRateLimited($userPoint)) {
            Log::warning("Rate limit exceeded", [
                'username' => $userPoint->user_identifier,
                'platform' => $userPoint->platform
            ]);
            return 0;
        }

        // 3. Vérifier si post déjà traité (idempotence)
        if ($this->isPostAlreadyProcessed($post->id)) {
            Log::info("Post already processed", ['post_id' => $post->id]);
            return 0;
        }

        $pointsAwarded = 0;
        
        // 4. Points de base pour le post
        $basePoints = $this->getConfig('points_per_post', 50);
        $pointsAwarded += $basePoints;
        $this->createTransaction($userPoint->id, $post->id, $basePoints, 'post');

        // 5. Bonus likes (si >= 10 likes)
        if ($post->likes_count >= 10) {
            $likeBonus = $this->getConfig('points_likes_bonus', 10);
            $pointsAwarded += $likeBonus;
            $this->createTransaction($userPoint->id, $post->id, $likeBonus, 'like_bonus');
        }

        // 6. Bonus premier post du jour
        if ($this->isFirstPostToday($userPoint)) {
            $firstPostBonus = $this->getConfig('points_first_post_day', 30);
            $pointsAwarded += $firstPostBonus;
            $this->createTransaction($userPoint->id, $post->id, $firstPostBonus, 'first_post_day');
        }

        // 7. Bonus streak (si 7+ jours consécutifs)
        $streakDays = $this->updateStreak($userPoint);
        if ($streakDays >= 7 && $streakDays % 7 == 0) {
            $streakBonus = $this->getConfig('points_streak_7days', 100);
            $pointsAwarded += $streakBonus;
            $this->createTransaction($userPoint->id, $post->id, $streakBonus, 'streak_bonus');
        }

        // 8. Bonus concours actif (si post pendant concours)
        $activeContest = $this->getActiveContestForHashtag($post->content);
        if ($activeContest) {
            $contestBonus = $this->getConfig('points_contest_participation', 50);
            $pointsAwarded += $contestBonus;
            $this->createTransaction($userPoint->id, $post->id, $contestBonus, 'contest_bonus', [
                'contest_id' => $activeContest->id
            ]);
            
            // Créer entrée concours
            $this->createContestEntry($activeContest, $userPoint, $post);
        }

        // 9. Mettre à jour points utilisateur
        DB::transaction(function() use ($userPoint, $pointsAwarded) {
            $userPoint->increment('total_points', $pointsAwarded);
            $userPoint->increment('weekly_points', $pointsAwarded);
            $userPoint->increment('monthly_points', $pointsAwarded);
            $userPoint->update(['last_post_at' => now()]);
        });

        // 10. Invalider cache leaderboard
        Cache::tags(['leaderboard'])->flush();

        // 11. Dispatcher event
        event(new \App\Events\PointsAwarded($userPoint, $pointsAwarded, $post));

        Log::info("Points awarded", [
            'post_id' => $post->id,
            'user' => $userPoint->user_identifier,
            'points' => $pointsAwarded
        ]);

        return $pointsAwarded;
    }

    /**
     * Obtenir ou créer UserPoint AUTOMATIQUEMENT
     * 
     * ✅ CRÉATION AUTOMATIQUE À LA VOLÉE
     * Quand un utilisateur poste avec le hashtag pour la première fois,
     * il est automatiquement créé dans le système de gamification.
     * Aucune inscription manuelle requise !
     * 
     * @param string $username - Instagram username, Facebook ID, Twitter handle
     * @param string $platform - 'instagram', 'facebook', 'twitter', 'google'
     * @return UserPoint|null
     */
    protected function getOrCreateUserPoint(string $username, string $platform): ?UserPoint
    {
        try {
            // Validation
            if (empty($username) || strlen($username) > 255) {
                throw new \InvalidArgumentException("Invalid username");
            }

            // Normalisation
            $username = $this->normalizeUsername($username);

            // Chercher utilisateur existant
            $userPoint = UserPoint::where('user_identifier', $username)
                ->where('platform', $platform)
                ->first();

            if ($userPoint) {
                // ✅ Utilisateur existe déjà → le retourner
                return $userPoint;
            }

            // ✅ Nouvel utilisateur → créer automatiquement !
            $userPoint = UserPoint::create([
                'user_identifier' => $username,
                'platform' => $platform,
                'total_points' => 0,
                'weekly_points' => 0,
                'monthly_points' => 0,
                'streak_days' => 0,
                'last_post_at' => null
            ]);

            // 🎉 Dispatcher event "Nouvel utilisateur créé"
            event(new \App\Events\UserPointCreated($userPoint));

            // 📝 Log création
            Log::info("🎉 New user created automatically", [
                'username' => $username,
                'platform' => $platform,
                'user_point_id' => $userPoint->id
            ]);

            // 🎁 Attribuer badge "Débutant" immédiatement
            $this->awardFirstBadge($userPoint);

            return $userPoint;

        } catch (\Exception $e) {
            Log::error("Failed to get/create user point", [
                'username' => $username,
                'platform' => $platform,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Normaliser username
     */
    protected function normalizeUsername(string $username): string
    {
        $username = trim($username);
        $username = strtolower($username);
        // Garder seulement lettres, chiffres, underscore, point
        $username = preg_replace('/[^a-z0-9_.]/', '', $username);
        return $username;
    }

    /**
     * Attribuer badge "Débutant" au premier post
     */
    protected function awardFirstBadge(UserPoint $userPoint): void
    {
        $beginnerBadge = \App\Models\Badge::where('key', 'beginner')->first();
        
        if ($beginnerBadge) {
            \App\Models\UserBadge::create([
                'user_point_id' => $userPoint->id,
                'badge_id' => $beginnerBadge->id,
                'unlocked_at' => now()
            ]);

            Log::info("🏅 First badge awarded", [
                'username' => $userPoint->user_identifier,
                'badge' => 'beginner'
            ]);
        }
    }

    /**
     * Créer transaction points
     */
    protected function createTransaction(
        int $userPointId,
        ?int $postId,
        int $points,
        string $type,
        array $metadata = []
    ): PointTransaction {
        return PointTransaction::create([
            'user_point_id' => $userPointId,
            'post_id' => $postId,
            'points_awarded' => $points,
            'transaction_type' => $type,
            'metadata' => !empty($metadata) ? $metadata : null
        ]);
    }

    /**
     * Vérifier si premier post aujourd'hui
     */
    protected function isFirstPostToday(UserPoint $userPoint): bool
    {
        if (!$userPoint->last_post_at) {
            return true;
        }

        return !$userPoint->last_post_at->isToday();
    }

    /**
     * Mettre à jour streak
     */
    protected function updateStreak(UserPoint $userPoint): int
    {
        if (!$userPoint->last_post_at) {
            $userPoint->update(['streak_days' => 1]);
            return 1;
        }

        $daysSinceLastPost = now()->diffInDays($userPoint->last_post_at);

        if ($daysSinceLastPost == 0) {
            // Même jour, pas de changement
            return $userPoint->streak_days;
        } elseif ($daysSinceLastPost == 1) {
            // Jour consécutif, incrémenter
            $newStreak = $userPoint->streak_days + 1;
            $userPoint->update(['streak_days' => $newStreak]);
            return $newStreak;
        } else {
            // Streak cassé, reset
            $userPoint->update(['streak_days' => 1]);
            return 1;
        }
    }

    /**
     * Obtenir configuration
     */
    protected function getConfig(string $key, int $default): int
    {
        return Cache::remember("gamification_config_{$key}", 3600, function() use ($key, $default) {
            $config = GamificationConfig::where('key', $key)->first();
            if (!$config) {
                return $default;
            }
            return $config->value['amount'] ?? $default;
        });
    }

    /**
     * Obtenir concours actif pour hashtag
     */
    protected function getActiveContestForHashtag(string $content): ?Contest
    {
        $activeContests = Cache::remember('active_contests', 300, function() {
            return Contest::active()->get();
        });

        return $activeContests->first(function($contest) use ($content) {
            return stripos($content, $contest->hashtag) !== false;
        });
    }

    /**
     * Créer entrée concours
     */
    protected function createContestEntry(Contest $contest, UserPoint $userPoint, Post $post): void
    {
        try {
            \App\Models\ContestEntry::create([
                'contest_id' => $contest->id,
                'user_point_id' => $userPoint->id,
                'post_id' => $post->id,
                'is_valid' => true
            ]);
        } catch (\Exception $e) {
            // Peut déjà exister (unique constraint)
            Log::debug("Contest entry already exists or failed", [
                'contest_id' => $contest->id,
                'post_id' => $post->id
            ]);
        }
    }

    /**
     * Vérifier rate limiting
     */
    protected function isRateLimited(UserPoint $userPoint): bool
    {
        $maxPostsPerDay = $this->getConfig('max_posts_per_day', 10);
        
        $postsToday = Post::where('author_username', $userPoint->user_identifier)
            ->where('platform', $userPoint->platform)
            ->whereDate('created_at', today())
            ->count();

        return $postsToday >= $maxPostsPerDay;
    }

    /**
     * Vérifier si post déjà traité
     */
    protected function isPostAlreadyProcessed(int $postId): bool
    {
        return PointTransaction::where('post_id', $postId)
            ->where('transaction_type', 'post')
            ->exists();
    }

    /**
     * Obtenir points utilisateur
     */
    public function getUserPoints(string $username, string $platform): ?UserPoint
    {
        return UserPoint::where('user_identifier', $username)
            ->where('platform', $platform)
            ->first();
    }

    /**
     * Reset points hebdomadaires (schedulé dimanche 00:00)
     */
    public function resetWeeklyPoints(): int
    {
        return UserPoint::query()->update(['weekly_points' => 0]);
    }

    /**
     * Reset points mensuels (schedulé 1er du mois 00:00)
     */
    public function resetMonthlyPoints(): int
    {
        return UserPoint::query()->update(['monthly_points' => 0]);
    }

    /**
     * Obtenir historique transactions
     */
    public function getTransactionHistory(int $userPointId, int $limit = 50): array
    {
        return PointTransaction::where('user_point_id', $userPointId)
            ->with('post')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Ajuster points manuellement (admin)
     */
    public function adjustPoints(UserPoint $userPoint, int $points, string $reason = ''): void
    {
        $this->createTransaction($userPoint->id, null, $points, 'admin_adjustment', [
            'reason' => $reason
        ]);

        $userPoint->increment('total_points', $points);
        $userPoint->increment('weekly_points', $points);
        $userPoint->increment('monthly_points', $points);

        Cache::tags(['leaderboard'])->flush();

        Log::info("Points adjusted manually", [
            'user' => $userPoint->user_identifier,
            'points' => $points,
            'reason' => $reason
        ]);
    }

    /**
     * Obtenir stats globales points
     */
    public function getStats(): array
    {
        return Cache::remember('points_stats', 300, function() {
            return [
                'total_points_distributed' => UserPoint::sum('total_points'),
                'average_points_per_user' => round(UserPoint::avg('total_points'), 2),
                'top_user' => UserPoint::orderBy('total_points', 'desc')->first(),
                'active_users_this_week' => UserPoint::where('weekly_points', '>', 0)->count(),
                'total_transactions' => PointTransaction::count()
            ];
        });
    }
}

