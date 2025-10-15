<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytic extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'event_type',
        'user_agent',
        'ip_address',
        'referrer',
        'duration',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public static function track(string $eventType, ?Post $post = null, array $metadata = [])
    {
        return static::create([
            'post_id' => $post?->id,
            'event_type' => $eventType,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'referrer' => request()->header('referer'),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    public static function getStats(string $period = 'week')
    {
        $date = match($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subWeek(),
        };

        return static::where('created_at', '>=', $date)
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();
    }
}

