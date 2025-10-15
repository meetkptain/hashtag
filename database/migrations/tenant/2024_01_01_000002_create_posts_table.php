<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->unique();
            $table->string('platform'); // instagram, facebook, twitter, google
            $table->text('content')->nullable();
            $table->string('author_name');
            $table->string('author_username')->nullable();
            $table->string('author_avatar')->nullable();
            $table->json('media')->nullable(); // images, videos
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->decimal('rating', 2, 1)->nullable(); // pour reviews
            $table->json('hashtags')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('posted_at');
            $table->boolean('is_new')->default(true); // pour gamification
            $table->boolean('is_highlighted')->default(false);
            $table->integer('display_count')->default(0);
            $table->timestamps();
            
            $table->index(['feed_id', 'posted_at']);
            $table->index('is_new');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

