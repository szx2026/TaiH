<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_test_id')->constrained()->cascadeOnDelete();
            $table->json('metrics');
            $table->text('conclusion');
            $table->text('adjustment_items');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('campaign_revisions'); }
};
