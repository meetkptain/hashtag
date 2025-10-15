<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analytic;
use App\Models\Post;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Get overall analytics
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'week');
        
        $stats = Analytic::getStats($period);
        
        // Get posts stats
        $totalPosts = Post::count();
        $newPosts = Post::where('is_new', true)->count();
        $highlightedPosts = Post::where('is_highlighted', true)->count();
        
        // Get engagement stats
        $totalEngagement = Post::sum(\DB::raw('likes_count + comments_count + shares_count'));
        $averageEngagement = $totalPosts > 0 ? $totalEngagement / $totalPosts : 0;
        
        // Get top posts
        $topPosts = Post::orderByRaw('likes_count + (comments_count * 2) + (shares_count * 3) DESC')
            ->limit(10)
            ->get(['id', 'content', 'author_name', 'platform', 'likes_count', 'comments_count', 'shares_count']);

        return response()->json([
            'period' => $period,
            'events' => $stats,
            'posts' => [
                'total' => $totalPosts,
                'new' => $newPosts,
                'highlighted' => $highlightedPosts,
            ],
            'engagement' => [
                'total' => $totalEngagement,
                'average' => round($averageEngagement, 2),
            ],
            'top_posts' => $topPosts,
        ]);
    }

    /**
     * Get analytics by platform
     */
    public function byPlatform(Request $request)
    {
        $period = $request->get('period', 'week');
        $date = match($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subWeek(),
        };

        $platformStats = Post::where('created_at', '>=', $date)
            ->selectRaw('platform, COUNT(*) as posts, SUM(likes_count) as likes, SUM(comments_count) as comments, SUM(shares_count) as shares')
            ->groupBy('platform')
            ->get();

        return response()->json([
            'period' => $period,
            'platforms' => $platformStats,
        ]);
    }

    /**
     * Get analytics timeline
     */
    public function timeline(Request $request)
    {
        $period = $request->get('period', 'week');
        $date = match($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subWeek(),
        };

        $groupBy = match($period) {
            'day' => 'HOUR(created_at)',
            'week' => 'DATE(created_at)',
            'month' => 'DATE(created_at)',
            'year' => 'MONTH(created_at)',
            default => 'DATE(created_at)',
        };

        $timeline = Analytic::where('created_at', '>=', $date)
            ->selectRaw("{$groupBy} as period, event_type, COUNT(*) as count")
            ->groupBy('period', 'event_type')
            ->orderBy('period')
            ->get()
            ->groupBy('period')
            ->map(function ($items) {
                return $items->pluck('count', 'event_type');
            });

        return response()->json([
            'period' => $period,
            'timeline' => $timeline,
        ]);
    }
}

