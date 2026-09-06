<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_projects')->orderBy('id')->each(function (object $project): void {
            DB::table('landing_pages')->where('product_project_id', $project->id)->update(['title' => $project->product_name]);
            DB::table('creative_assets')->where('product_project_id', $project->id)->update(['title' => $project->product_name]);
            DB::table('campaign_tests')->where('product_project_id', $project->id)->update(['campaign_name' => $project->product_name]);
        });
    }

    public function down(): void
    {
        // Product titles are now the canonical, shared artifact name.
    }
};
