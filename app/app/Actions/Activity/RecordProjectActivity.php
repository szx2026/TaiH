<?php

namespace App\Actions\Activity;

use App\Models\ProductProject;
use App\Models\ProjectActivity;
use App\Models\User;

class RecordProjectActivity
{
    public function handle(ProductProject $project, User $actor, string $event, array $payload = []): ProjectActivity
    {
        return ProjectActivity::create([
            'product_project_id' => $project->id,
            'actor_id' => $actor->id,
            'event' => $event,
            'payload' => $payload,
        ]);
    }
}
