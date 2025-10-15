<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\WidgetSetting;
use App\Models\Analytic;
use App\Services\FeedService;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    protected FeedService $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Get widget configuration and initial data
     */
    public function config(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->where('active', true)->first();
        
        if (!$tenant) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Switch to tenant database
        $tenant->switchDatabase();

        // Get widget settings
        $settings = WidgetSetting::firstOrCreate([], [
            'theme' => 'light',
            'direction' => 'vertical',
            'speed' => 'medium',
            'gamification_enabled' => $tenant->hasGamification(),
            'fullscreen_enabled' => true,
            'autoplay' => true,
            'posts_per_view' => 3,
        ]);

        return response()->json([
            'tenant' => [
                'name' => $tenant->name,
                'branding' => $tenant->branding,
            ],
            'settings' => [
                'theme' => $settings->theme,
                'direction' => $settings->direction,
                'speed' => $settings->speed,
                'scroll_speed' => $settings->getScrollSpeed(),
                'gamification_enabled' => $settings->gamification_enabled,
                'fullscreen_enabled' => $settings->fullscreen_enabled,
                'autoplay' => $settings->autoplay,
                'posts_per_view' => $settings->posts_per_view,
                'colors' => $settings->getCustomColors(),
            ],
        ]);
    }

    /**
     * Get posts for the widget
     */
    public function posts(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->where('active', true)->first();
        
        if (!$tenant) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Switch to tenant database
        $tenant->switchDatabase();

        // Get filters
        $filters = [
            'platform' => $request->get('platform'),
            'hashtags' => $request->get('hashtags'),
            'highlighted' => $request->get('highlighted'),
            'new' => $request->get('new'),
        ];

        $limit = min($request->get('limit', 50), 100);

        $posts = $this->feedService->getWidgetPosts($limit, $filters);

        // Track analytics
        Analytic::track('widget_load');

        return response()->json([
            'posts' => $posts,
            'count' => count($posts),
        ]);
    }

    /**
     * Track post view
     */
    public function trackView(Request $request, $postId)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->where('active', true)->first();
        
        if (!$tenant) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $tenant->switchDatabase();

        $post = \App\Models\Post::find($postId);
        
        if ($post) {
            $post->markAsViewed();
            Analytic::track('view', $post);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Track post interaction
     */
    public function trackInteraction(Request $request, $postId)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->where('active', true)->first();
        
        if (!$tenant) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $tenant->switchDatabase();

        $post = \App\Models\Post::find($postId);
        $eventType = $request->get('type', 'click');
        
        if ($post) {
            Analytic::track($eventType, $post, [
                'duration' => $request->get('duration'),
            ]);
        }

        return response()->json(['success' => true]);
    }
}

