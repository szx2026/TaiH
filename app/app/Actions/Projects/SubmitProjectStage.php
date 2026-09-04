<?php

namespace App\Actions\Projects;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\Department;
use App\Models\User;
use App\Models\WorkflowTransition;
use Illuminate\Support\Facades\DB;

class SubmitProjectStage
{
    public function handle(ProductProject $project, User $actor, string $targetStage, ?string $note): WorkflowTransition
    {
        return DB::transaction(function () use ($project, $actor, $targetStage, $note): WorkflowTransition {
            $fromStage = $project->current_stage;

            $project->update([
                'current_stage' => $targetStage,
                'status' => 'in_progress',
                'owner_department_id' => Department::query()->where('code', $targetStage)->value('id') ?? $project->owner_department_id,
            ]);

            $transition = WorkflowTransition::create([
                'product_project_id' => $project->id,
                'from_stage' => $fromStage,
                'to_stage' => $targetStage,
                'action' => 'submit',
                'note' => $note,
                'operator_id' => $actor->id,
            ]);

            app(RecordProjectActivity::class)->handle($project, $actor, 'stage.advanced', [
                'from_stage' => $fromStage,
                'to_stage' => $targetStage,
                'note' => $note,
                'transition_id' => $transition->id,
            ]);

            return $transition;
        });
    }
}
