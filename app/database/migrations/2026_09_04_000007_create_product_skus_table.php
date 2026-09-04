<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_source_id')->constrained()->cascadeOnDelete();
            $table->string('sku_code');
            $table->string('variant_name');
            $table->string('sku_status')->default('imported');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['product_source_id', 'sku_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_skus');
    }
};
