<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->text('description');
            $table->enum('category', [
                'progression',  // Basé sur volume (10 posts, 50 posts)
                'social',       // Basé sur engagement (likes, shares)
                'event',        // Temporaire (Halloween, Noël)
                'challenge',    // Objectifs spécifiques (streak, night owl)
                'exclusive',    // Top performers (top 1, top 3)
                'secret'        // Critères cachés (post #7777)
            ]);
            $table->string('icon_url', 500)->nullable();
            $table->text('icon_svg')->nullable();
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->json('criteria'); // Ex: {"type": "posts_count", "min_posts": 10}
            $table->boolean('active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            
            $table->index('category');
            $table->index('rarity');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};

