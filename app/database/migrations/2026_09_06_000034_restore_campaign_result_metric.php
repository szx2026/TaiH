<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_tests', function (Blueprint $table) {
            $table->string('result_metric', 20)->default('purchase')->after('purchase_value');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_tests', function (Blueprint $table) {
            $table->dropColumn('result_metric');
        });
    }
};
