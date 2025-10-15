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
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->string('user_identifier'); // Instagram username, Facebook ID, Twitter handle
            $table->enum('platform', ['instagram', 'facebook', 'twitter', 'google']);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('weekly_points')->default(0);
            $table->unsignedInteger('monthly_points')->default(0);
            $table->timestamp('last_post_at')->nullable();
            $table->unsignedInteger('streak_days')->default(0);
            $table->timestamps();
            
            // Indexes pour performance leaderboards
            $table->index('total_points');
            $table->index('weekly_points');
            $table->index('monthly_points');
            $table->index(['user_identifier', 'platform']);
            
            // Clé unique : user + platform
            $table->unique(['user_identifier', 'platform'], 'unique_user_platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_points');
    }
};

