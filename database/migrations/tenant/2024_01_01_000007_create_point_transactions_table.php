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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_point_id')->constrained('user_points')->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained('posts')->onDelete('set null');
            $table->integer('points_awarded'); // Peut être négatif (pénalité admin)
            $table->enum('transaction_type', [
                'post', 
                'like_bonus', 
                'first_post_day', 
                'streak_bonus', 
                'contest_bonus', 
                'admin_adjustment'
            ]);
            $table->json('metadata')->nullable(); // Extra info (contest_id, badge_id, etc.)
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('user_point_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};

