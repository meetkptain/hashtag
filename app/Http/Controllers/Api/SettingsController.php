<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Get widget settings
     */
    public function index()
    {
        $settings = WidgetSetting::firstOrCreate([]);

        return response()->json([
            'settings' => $settings,
        ]);
    }

    /**
     * Update widget settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'sometimes|in:light,dark,custom',
            'direction' => 'sometimes|in:vertical,horizontal',
            'speed' => 'sometimes|in:slow,medium,fast',
            'gamification_enabled' => 'sometimes|boolean',
            'fullscreen_enabled' => 'sometimes|boolean',
            'autoplay' => 'sometimes|boolean',
            'posts_per_view' => 'sometimes|integer|min:1|max:10',
            'colors' => 'sometimes|array',
            'fonts' => 'sometimes|array',
            'custom_css' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = WidgetSetting::firstOrCreate([]);
        $settings->update($request->all());

        return response()->json([
            'settings' => $settings,
            'message' => 'Settings updated successfully',
        ]);
    }
}

