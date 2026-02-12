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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopify_shop_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('shopify_id')->nullable();
            $table->string('title');
            $table->text('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('handle')->nullable();
            $table->string('status')->default('draft');
            $table->json('tags')->nullable();
            $table->json('options')->nullable();
            $table->string('template_suffix')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('shopify_data')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['shopify_shop_id', 'shopify_id']);
            $table->index(['shopify_shop_id', 'status']);
            $table->index('title');
            $table->index('vendor');
            $table->index('product_type');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
