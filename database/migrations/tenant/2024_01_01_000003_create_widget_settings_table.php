<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->string('theme')->default('light'); // light, dark, custom
            $table->string('direction')->default('vertical'); // vertical, horizontal
            $table->string('speed')->default('medium'); // slow, medium, fast
            $table->boolean('gamification_enabled')->default(false);
            $table->boolean('fullscreen_enabled')->default(true);
            $table->boolean('autoplay')->default(true);
            $table->integer('posts_per_view')->default(3);
            $table->json('colors')->nullable(); // custom colors
            $table->json('fonts')->nullable(); // custom fonts
            $table->string('custom_css')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_settings');
    }
};

