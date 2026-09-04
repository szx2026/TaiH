<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('product_name');
            $table->string('category')->nullable();
            $table->string('market', 16);
            $table->string('priority')->default('medium');
            $table->string('current_stage')->default('market_research');
            $table->string('status')->default('draft');
            $table->foreignId('owner_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_projects');
    }
};
