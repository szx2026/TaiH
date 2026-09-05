<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_sources', function (Blueprint $table) {
            $table->string('custom_source_name')->nullable()->after('platform');
        });
    }

    public function down(): void
    {
        Schema::table('research_sources', function (Blueprint $table) {
            $table->dropColumn('custom_source_name');
        });
    }
};
