<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_sku_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['landing_page_id', 'product_sku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_skus');
    }
};
