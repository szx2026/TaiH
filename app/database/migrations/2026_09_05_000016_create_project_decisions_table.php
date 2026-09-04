<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_project_id')->constrained()->cascadeOnDelete();
            $table->string('decision_type', 40);
            $table->string('title');
            $table->string('status', 24)->default('open');
            $table->json('details')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['product_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_decisions');
    }
};
