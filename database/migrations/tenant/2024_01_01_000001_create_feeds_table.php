<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // instagram, facebook, twitter, google_reviews
            $table->json('config'); // hashtags, pages, etc.
            $table->json('credentials')->nullable(); // API tokens specifiques
            $table->integer('refresh_interval')->default(300); // secondes
            $table->boolean('active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->integer('posts_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};

