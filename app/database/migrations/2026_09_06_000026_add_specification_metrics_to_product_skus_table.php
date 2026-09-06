<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->decimal('purchase_price', 10, 2)->nullable()->after('variant_name');
            $table->unsignedInteger('weight_g')->nullable()->after('purchase_price');
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'weight_g']);
        });
    }
};
