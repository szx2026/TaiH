<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_conversions')->nullable()->after('checkout_conversions');
            $table->decimal('purchase_value', 12, 2)->nullable()->after('purchase_conversions');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_tests', function (Blueprint $table) {
            $table->dropColumn(['purchase_conversions', 'purchase_value']);
        });
    }
};
