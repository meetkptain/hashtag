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
        Schema::create('gamification_config', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('value'); // {"amount": 50}
            $table->text('description')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
        
        // Insérer configuration par défaut
        DB::table('gamification_config')->insert([
            ['key' => 'points_per_post', 'value' => json_encode(['amount' => 50]), 'description' => 'Points de base par post'],
            ['key' => 'points_likes_bonus', 'value' => json_encode(['amount' => 10, 'min_likes' => 10]), 'description' => 'Bonus si post a 10+ likes'],
            ['key' => 'points_first_post_day', 'value' => json_encode(['amount' => 30]), 'description' => 'Bonus premier post du jour'],
            ['key' => 'points_streak_7days', 'value' => json_encode(['amount' => 100]), 'description' => 'Bonus streak 7 jours'],
            ['key' => 'points_contest_participation', 'value' => json_encode(['amount' => 50]), 'description' => 'Bonus participation concours'],
            ['key' => 'max_posts_per_day', 'value' => json_encode(['limit' => 10]), 'description' => 'Limite posts par jour (anti-spam)'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_config');
    }
};

