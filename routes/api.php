<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\SettingsController;

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
});

