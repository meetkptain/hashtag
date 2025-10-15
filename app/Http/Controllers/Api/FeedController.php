<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedController extends Controller
{
    protected FeedService $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Display a listing of feeds
     */
    public function index()
    {
        $feeds = Feed::with('posts')->get()->map(function ($feed) {
            return [
                'id' => $feed->id,
                'name' => $feed->name,
                'type' => $feed->type,
                'config' => $feed->config,
                'active' => $feed->active,
                'posts_count' => $feed->posts_count,
                'last_fetched_at' => $feed->last_fetched_at?->toIso8601String(),
                'stats' => $this->feedService->getFeedStats($feed),
            ];
        });

        return response()->json([
            'feeds' => $feeds,
        ]);
    }

    /**
     * Store a new feed
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:instagram,facebook,twitter,google_reviews',
            'config' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if tenant can add more feeds
        $tenant = $request->user()->tenant;
        
        if (!$tenant->canAddFeed()) {
            return response()->json([
                'error' => 'Feed limit reached for your plan',
            ], 403);
        }

        // Validate provider config
        $providerClass = config("feeds.providers.{$request->type}");
        
        if (!$providerClass) {
            return response()->json(['error' => 'Invalid feed type'], 400);
        }

        $provider = app($providerClass);
        
        if (!$provider->validateConfig($request->config)) {
            return response()->json(['error' => 'Invalid configuration'], 400);
        }

        $feed = Feed::create([
            'name' => $request->name,
            'type' => $request->type,
            'config' => $request->config,
            'active' => true,
        ]);

        // Sync immediately
        $this->feedService->syncFeed($feed);

        return response()->json([
            'feed' => $feed,
            'message' => 'Feed created successfully',
        ], 201);
    }

    /**
     * Display a specific feed
     */
    public function show(Feed $feed)
    {
        return response()->json([
            'feed' => [
                'id' => $feed->id,
                'name' => $feed->name,
                'type' => $feed->type,
                'config' => $feed->config,
                'active' => $feed->active,
                'posts_count' => $feed->posts_count,
                'last_fetched_at' => $feed->last_fetched_at?->toIso8601String(),
                'stats' => $this->feedService->getFeedStats($feed),
            ],
        ]);
    }

    /**
     * Update a feed
     */
    public function update(Request $request, Feed $feed)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'config' => 'sometimes|array',
            'active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $feed->update($request->only(['name', 'config', 'active']));

        return response()->json([
            'feed' => $feed,
            'message' => 'Feed updated successfully',
        ]);
    }

    /**
     * Delete a feed
     */
    public function destroy(Feed $feed)
    {
        $feed->delete();

        return response()->json([
            'message' => 'Feed deleted successfully',
        ]);
    }

    /**
     * Sync a feed manually
     */
    public function sync(Feed $feed)
    {
        $newPostsCount = $this->feedService->syncFeed($feed);

        return response()->json([
            'message' => "Feed synced successfully. {$newPostsCount} new posts.",
            'new_posts' => $newPostsCount,
        ]);
    }
}

