<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\FeedConnectionController;
use App\Http\Controllers\StripeController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Social Authentication routes
Route::get('auth/{provider}', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect')
    ->where('provider', 'facebook|google|twitter|instagram');

Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback')
    ->where('provider', 'facebook|google|twitter|instagram');

// Dashboard routes (authenticated)
Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');
    
    Route::get('/feeds', function () {
        return Inertia::render('Dashboard/Feeds');
    })->name('feeds');
    
    Route::get('/analytics', function () {
        return Inertia::render('Dashboard/Analytics');
    })->name('analytics');
    
    Route::get('/settings', function () {
        return Inertia::render('Dashboard/Settings');
    })->name('settings');
    
    Route::get('/billing', function () {
        return Inertia::render('Dashboard/Billing');
    })->name('billing');
    
    // Stripe routes
    Route::post('/stripe/checkout', [StripeController::class, 'createCheckoutSession']);
    Route::post('/stripe/portal', [StripeController::class, 'createPortalSession']);
    Route::get('/stripe/subscription', [StripeController::class, 'getSubscription']);
    Route::post('/stripe/addon/checkout', [StripeController::class, 'createAddonCheckout'])->name('stripe.addon.checkout');
    
    // Feed Social Account Connection
    Route::prefix('connect')->group(function () {
        // Instagram
        Route::get('/instagram', [FeedConnectionController::class, 'connectInstagram'])->name('feed.connect.instagram');
        Route::get('/instagram/callback', [FeedConnectionController::class, 'callbackInstagram'])->name('feed.connect.instagram.callback');
        
        // Facebook
        Route::get('/facebook', [FeedConnectionController::class, 'connectFacebook'])->name('feed.connect.facebook');
        Route::get('/facebook/callback', [FeedConnectionController::class, 'callbackFacebook'])->name('feed.connect.facebook.callback');
        
        // Twitter
        Route::get('/twitter', [FeedConnectionController::class, 'connectTwitter'])->name('feed.connect.twitter');
        Route::get('/twitter/callback', [FeedConnectionController::class, 'callbackTwitter'])->name('feed.connect.twitter.callback');
    });
    
    // Disconnect & status
    Route::post('/feeds/{feed}/disconnect', [FeedConnectionController::class, 'disconnect'])->name('feed.disconnect');
    Route::get('/feeds/{feed}/connection-status', [FeedConnectionController::class, 'status'])->name('feed.connection.status');
});

// Stripe webhook (public)
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Widget embed script
Route::get('/widget.js', function () {
    return response()
        ->file(public_path('widget.js'))
        ->header('Content-Type', 'application/javascript');
})->name('widget.script');

