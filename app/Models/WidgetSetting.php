<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetSetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'theme',
        'direction',
        'speed',
        'gamification_enabled',
        'fullscreen_enabled',
        'autoplay',
        'posts_per_view',
        'colors',
        'fonts',
        'custom_css',
    ];

    protected $casts = [
        'gamification_enabled' => 'boolean',
        'fullscreen_enabled' => 'boolean',
        'autoplay' => 'boolean',
        'colors' => 'array',
        'fonts' => 'array',
    ];

    public function getScrollSpeed(): int
    {
        return match($this->speed) {
            'slow' => 8000,
            'medium' => 5000,
            'fast' => 3000,
            default => 5000,
        };
    }

    public function getCustomColors(): array
    {
        return $this->colors ?? [
            'primary' => '#3b82f6',
            'secondary' => '#8b5cf6',
            'background' => '#ffffff',
            'text' => '#1f2937',
            'accent' => '#f59e0b',
        ];
    }
}

