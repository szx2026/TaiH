<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('product_projects', function (Blueprint $table) { $table->string('outcome')->nullable(); $table->text('outcome_reason')->nullable(); $table->text('next_action')->nullable(); $table->timestamp('outcome_recorded_at')->nullable(); $table->foreignId('outcome_recorded_by')->nullable()->constrained('users')->nullOnDelete(); }); }
    public function down(): void { Schema::table('product_projects', function (Blueprint $table) { $table->dropConstrainedForeignId('outcome_recorded_by'); $table->dropColumn(['outcome', 'outcome_reason', 'next_action', 'outcome_recorded_at']); }); }
};
