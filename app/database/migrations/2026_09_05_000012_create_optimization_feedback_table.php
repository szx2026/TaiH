<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimization_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_test_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_stage');
            $table->text('note');
            $table->string('status')->default('open');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optimization_feedback');
    }
};
