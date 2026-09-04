<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_decisions', function (Blueprint $table) {
            $table->string('requested_from_stage', 40)->default('website_operations')->after('decision_type');
        });
    }

    public function down(): void
    {
        Schema::table('project_decisions', function (Blueprint $table) {
            $table->dropColumn('requested_from_stage');
        });
    }
};
