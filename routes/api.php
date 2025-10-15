<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\GamificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public widget routes (API key authentication)
Route::prefix('widget')->group(function () {
    Route::get('/config', [WidgetController::class, 'config']);
    Route::get('/posts', [WidgetController::class, 'posts']);
    Route::post('/posts/{post}/view', [WidgetController::class, 'trackView']);
    Route::post('/posts/{post}/interaction', [WidgetController::class, 'trackInteraction']);
});

// Protected routes (Sanctum authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Feeds management
    Route::apiResource('feeds', FeedController::class);
    Route::post('feeds/{feed}/sync', [FeedController::class, 'sync']);
    
    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index']);
        Route::get('/platform', [AnalyticsController::class, 'byPlatform']);
        Route::get('/timeline', [AnalyticsController::class, 'timeline']);
    });
    
    // Widget settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::put('/', [SettingsController::class, 'update']);
    });
    
    // Feed connection status
    Route::get('feeds/{feed}/connection-status', [\App\Http\Controllers\FeedConnectionController::class, 'status']);
    
    // Tenant add-ons
    Route::get('/tenant/addons', function() {
        $addons = \App\Models\TenantAddon::where('active', true)->get();
        return response()->json(['addons' => $addons]);
    });
    
    // ═══════════════════════════════════════════════════════════
    // GAMIFICATION ROUTES (NOUVEAU - v1.2.0)
    // ═══════════════════════════════════════════════════════════
    
    // Leaderboard
    Route::prefix('leaderboard')->group(function () {
        Route::get('/global', [LeaderboardController::class, 'global']);
        Route::get('/weekly', [LeaderboardController::class, 'weekly']);
        Route::get('/monthly', [LeaderboardController::class, 'monthly']);
        Route::get('/position', [LeaderboardController::class, 'position']);
        Route::get('/stats', [LeaderboardController::class, 'stats']);
    });
    
    // Gamification
    Route::prefix('gamification')->group(function () {
        Route::get('/user', [GamificationController::class, 'getUser']);
        Route::get('/user/badges', [GamificationController::class, 'getUserBadges']);
        Route::get('/user/progress', [GamificationController::class, 'getUserProgress']);
        Route::post('/badge/mark-viewed', [GamificationController::class, 'markBadgeViewed']);
        Route::get('/stats', [GamificationController::class, 'stats']);
    });
});

// ═══════════════════════════════════════════════════════════
// GAMIFICATION PUBLIC ROUTES (Widget)
// ═══════════════════════════════════════════════════════════

Route::prefix('widget/gamification')->group(function () {
    Route::get('/leaderboard', function(\Illuminate\Http\Request $request) {
        $type = $request->input('type', 'weekly');
        $limit = $request->input('limit', 10);
        
        $service = app(\App\Services\Gamification\LeaderboardService::class);
        
        $leaderboard = match($type) {
            'global' => $service->getGlobalLeaderboard($limit),
            'monthly' => $service->getMonthlyLeaderboard($limit),
            default => $service->getWeeklyLeaderboard($limit)
        };
        
        return response()->json([
            'leaderboard' => $leaderboard,
            'type' => $type
        ]);
    });
    
    Route::get('/user/{username}', function(string $username, \Illuminate\Http\Request $request) {
        $platform = $request->input('platform', 'instagram');
        
        $userPoint = \App\Models\UserPoint::where('user_identifier', $username)
            ->where('platform', $platform)
            ->first();
        
        if (!$userPoint) {
            return response()->json(['exists' => false]);
        }
        
        return response()->json([
            'exists' => true,
            'points' => $userPoint->total_points,
            'weekly_points' => $userPoint->weekly_points,
            'rank' => $userPoint->rank,
            'streak_days' => $userPoint->streak_days,
            'badge_count' => $userPoint->badges()->count()
        ]);
    });
});

