<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->json('profit_data')->nullable()->after('product_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->dropColumn('profit_data');
        });
    }
};
