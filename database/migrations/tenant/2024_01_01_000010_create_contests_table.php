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
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('hashtag', 100);
            $table->text('prize');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->enum('status', ['draft', 'active', 'ended', 'drawn'])->default('draft');
            $table->unsignedInteger('winners_count')->default(1);
            $table->json('criteria')->nullable(); // {"min_followers": 100, "platforms": ["instagram"]}
            $table->timestamps();
            
            $table->index('status');
            $table->index(['start_at', 'end_at']);
            $table->index('hashtag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};

