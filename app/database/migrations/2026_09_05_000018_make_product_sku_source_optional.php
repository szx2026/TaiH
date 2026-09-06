<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            // MySQL uses the composite unique index to enforce this foreign key,
            // so release the constraint before removing the index.
            $table->dropForeign(['product_source_id']);
            $table->dropUnique('product_skus_product_source_id_sku_code_unique');
            $table->foreignId('product_source_id')->nullable()->change();
            $table->foreign('product_source_id')
                ->references('id')
                ->on('product_sources')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->foreignId('product_source_id')->nullable(false)->change();
            $table->unique(['product_source_id', 'sku_code']);
        });
    }
};
