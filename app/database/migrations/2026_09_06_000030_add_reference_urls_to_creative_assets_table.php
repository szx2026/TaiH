<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creative_assets', function (Blueprint $table) {
            $table->json('reference_urls')->nullable()->after('external_url');
        });
    }

    public function down(): void
    {
        Schema::table('creative_assets', function (Blueprint $table) {
            $table->dropColumn('reference_urls');
        });
    }
};
