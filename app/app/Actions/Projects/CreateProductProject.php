<?php

namespace App\Actions\Projects;

use App\Models\ProductProject;
use App\Models\User;
use Illuminate\Support\Str;

class CreateProductProject
{
    /** @param array{product_name: string, category?: string|null, market: string, priority: string} $data */
    public function handle(User $actor, array $data): ProductProject
    {
        return ProductProject::create([
            'project_code' => $this->nextProjectCode(),
            'product_name' => $data['product_name'],
            'category' => $data['category'] ?? null,
            'market' => $data['market'],
            'priority' => $data['priority'],
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $actor->department_id,
            'owner_user_id' => $actor->id,
            'created_by' => $actor->id,
        ]);
    }

    private function nextProjectCode(): string
    {
        do {
            $code = 'PP-'.now()->format('Ym').'-'.Str::upper(Str::random(6));
        } while (ProductProject::where('project_code', $code)->exists());

        return $code;
    }
}
