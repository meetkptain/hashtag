<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationConfig extends Model
{
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = null;

    protected $table = 'gamification_config';

    protected $fillable = [
        'key',
        'value',
        'description'
    ];

    protected $casts = [
        'value' => 'array'
    ];

    /**
     * Obtenir valeur config
     */
    public static function getValue(string $key, $default = null)
    {
        $config = static::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Définir valeur config
     */
    public static function setValue(string $key, array $value, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Obtenir toute la configuration sous forme de tableau
     */
    public static function getAll(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}

