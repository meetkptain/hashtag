<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_addons', function (Blueprint $table) {
            $table->id();
            $table->string('addon_key'); // instagram_connection, facebook_connection
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable(); // infos supplémentaires
            $table->timestamp('activated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('addon_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_addons');
    }
};

