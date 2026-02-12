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
        Schema::create('shopify_shops', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_shop_id')->unique();
            $table->string('domain')->unique();
            $table->string('shopify_domain')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('access_token')->nullable();
            $table->json('scopes')->nullable();
            $table->string('plan_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('uninstalled_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('is_active');
            $table->index('installed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_shops');
    }
};
