<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_decisions', function (Blueprint $table): void {
            $table->text('response_note')->nullable()->after('details');
            $table->foreignId('responded_by')->nullable()->after('response_note')->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable()->after('responded_by');
        });
    }

    public function down(): void
    {
        Schema::table('project_decisions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('responded_by');
            $table->dropColumn(['response_note', 'responded_at']);
        });
    }
};
