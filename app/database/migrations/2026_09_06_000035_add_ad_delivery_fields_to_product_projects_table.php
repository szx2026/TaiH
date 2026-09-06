<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->string('ad_delivery_status', 20)->default('not_started')->after('status');
            $table->timestamp('ad_started_at')->nullable()->after('ad_delivery_status');
        });
    }

    public function down(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->dropColumn(['ad_delivery_status', 'ad_started_at']);
        });
    }
};
