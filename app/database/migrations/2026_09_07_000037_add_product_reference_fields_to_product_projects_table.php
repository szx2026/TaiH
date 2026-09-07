<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->string('keywords', 1000)->nullable()->after('product_name');
            $table->string('detail_reference_url', 2048)->nullable()->after('keywords');
        });
    }

    public function down(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->dropColumn(['keywords', 'detail_reference_url']);
        });
    }
};
