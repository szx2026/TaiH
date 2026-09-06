<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_projects', function (Blueprint $table) {
            $table->timestamp('released_at')->nullable()->after('product_name');
            $table->string('product_image_path')->nullable()->after('released_at');
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('detail_image_path')->nullable()->after('page_url');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn('detail_image_path');
        });
        Schema::table('product_projects', function (Blueprint $table) {
            $table->dropColumn(['released_at', 'product_image_path']);
        });
    }
};
