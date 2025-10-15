<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// Schedule feed synchronization every 5 minutes
Schedule::command('feeds:sync')->everyFiveMinutes();

// Clean old analytics data monthly
Schedule::command('analytics:clean')->monthly();

// Refresh social tokens daily
Schedule::command('tokens:refresh')->daily();

