<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->string('creative_reference_url', 2048)->nullable()->after('detail_reference_url');
        });
    }

    public function down(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->dropColumn('creative_reference_url');
        });
    }
};
