<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_tests', function (Blueprint $table) {
            $table->decimal('cost_per_click', 12, 2)->nullable()->after('spend');
            $table->unsignedBigInteger('add_to_cart_conversions')->nullable()->after('conversions');
            $table->unsignedBigInteger('checkout_conversions')->nullable()->after('add_to_cart_conversions');
            $table->string('detail_image_path')->nullable()->after('checkout_conversions');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_tests', function (Blueprint $table) {
            $table->dropColumn(['cost_per_click', 'add_to_cart_conversions', 'checkout_conversions', 'detail_image_path']);
        });
    }
};
