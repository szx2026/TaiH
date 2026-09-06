<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->string('sku_code')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('product_skus')->whereNull('sku_code')->update(['sku_code' => 'PENDING']);

        Schema::table('product_skus', function (Blueprint $table) {
            $table->string('sku_code')->nullable(false)->change();
        });
    }
};
