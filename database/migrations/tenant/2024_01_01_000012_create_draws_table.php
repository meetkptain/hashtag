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
        Schema::create('draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained('contests')->onDelete('cascade');
            $table->foreignId('winner_user_point_id')->constrained('user_points')->onDelete('cascade');
            $table->foreignId('winner_post_id')->constrained('posts')->onDelete('cascade');
            $table->unsignedInteger('rank'); // 1 = 1er gagnant, 2 = 2ème, etc.
            $table->timestamp('drawn_at')->useCurrent();
            $table->string('random_seed'); // Pour provably fair
            
            $table->index('contest_id');
            $table->unique(['contest_id', 'rank'], 'unique_contest_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draws');
    }
};

