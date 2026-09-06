<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\OptimizationFeedback;
use App\Models\ProductProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OptimizationFeedbackController extends Controller
{
    public function update(Request $request, ProductProject $project, OptimizationFeedback $feedback): RedirectResponse
    {
        abort_unless($feedback->product_project_id === $project->id, 404);
        abort_unless(
            $request->user()?->department?->code === $feedback->target_stage
                || $request->user()?->hasRole('administrator'),
            403,
        );

        $data = $request->validate([
            'status' => ['required', Rule::in(['in_progress', 'resolved'])],
            'response_note' => ['required', 'string'],
        ]);

        $feedback->update([
            'status' => $data['status'],
            'response_note' => $data['response_note'],
            'resolved_by' => $data['status'] === 'resolved' ? $request->user()->id : null,
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'feedback.'.$data['status'], [
            'feedback_id' => $feedback->id,
            'target_stage' => $feedback->target_stage,
            'response_note' => $data['response_note'],
        ]);

        return to_route('projects.index');
    }
}
