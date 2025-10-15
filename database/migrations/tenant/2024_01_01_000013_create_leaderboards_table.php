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
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['all_time', 'monthly', 'weekly']);
            $table->string('period', 50); // '2025-10', '2025-W43'
            $table->foreignId('user_point_id')->constrained('user_points')->onDelete('cascade');
            $table->unsignedInteger('rank');
            $table->unsignedInteger('points');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['type', 'period', 'rank']);
            $table->unique(['type', 'period', 'user_point_id'], 'unique_leaderboard_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};

