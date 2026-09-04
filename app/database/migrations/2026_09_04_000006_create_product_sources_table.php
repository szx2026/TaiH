<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_project_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_url', 2048);
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('CNY');
            $table->unsignedInteger('weight_g')->nullable();
            $table->string('supplier_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sources');
    }
};
