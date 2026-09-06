<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->timestamp('ad_delivery_status_updated_at')->nullable()->after('ad_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->dropColumn('ad_delivery_status_updated_at');
        });
    }
};
