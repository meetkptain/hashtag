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
        Schema::create('contest_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained('contests')->onDelete('cascade');
            $table->foreignId('user_point_id')->constrained('user_points')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->timestamp('entry_date')->useCurrent();
            $table->boolean('is_valid')->default(true); // Si respecte critères
            
            $table->index('contest_id');
            $table->index('user_point_id');
            $table->unique(['contest_id', 'post_id'], 'unique_contest_post');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_entries');
    }
};

