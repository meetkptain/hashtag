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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_point_id')->constrained('user_points')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->timestamp('unlocked_at')->useCurrent();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            
            $table->unique(['user_point_id', 'badge_id'], 'unique_user_badge');
            $table->index('unlocked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};

