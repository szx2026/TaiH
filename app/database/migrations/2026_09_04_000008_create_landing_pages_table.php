<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title');
            $table->string('page_url', 2048);
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('specifications')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['product_project_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
