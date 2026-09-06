<?php

namespace App\Http\Controllers;

use App\Actions\Activity\RecordProjectActivity;
use App\Models\ProductProject;
use App\Models\ProjectDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectDecisionController extends Controller
{
    public function store(Request $request, ProductProject $project): RedirectResponse
    {
        abort_unless($request->user()?->department?->code === 'market_research' || $request->user()?->hasRole('administrator'), 403);

        $data = $request->validate([
            'decision_type' => ['required', Rule::in(['sku', 'specification'])],
            'requested_from_stage' => ['required', Rule::in(['website_operations'])],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:4000'],
        ]);

        $department = $request->user()?->department?->code;
        abort_unless(
            $request->user()?->hasRole('administrator')
            || ($department === 'market_research' && $data['requested_from_stage'] === 'website_operations'),
            403,
        );

        if ($data['decision_type'] === 'specification') {
            abort_unless(
                $project->skus()->whereNotNull('sku_code')->exists(),
                422,
                '请先根据已录入的产品规格回填公司内部 SKU，再发送运营部确认。',
            );
        }

        $decision = ProjectDecision::create([
            'product_project_id' => $project->id,
            'decision_type' => $data['decision_type'],
            'requested_from_stage' => $data['requested_from_stage'],
            'title' => $data['title'],
            'status' => 'open',
            'details' => ['note' => $data['details'] ?? null],
            'created_by' => $request->user()->id,
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'decision.created', [
            'decision_id' => $decision->id,
            'decision_type' => $decision->decision_type,
            'requested_from_stage' => $decision->requested_from_stage,
            'title' => $decision->title,
        ]);

        return to_route('projects.index', ['stage' => $department, 'project' => $project]);
    }

    public function respond(Request $request, ProductProject $project, ProjectDecision $decision): RedirectResponse
    {
        abort_unless($decision->product_project_id === $project->id, 404);
        abort_unless(
            ($request->user()?->department?->code === $decision->requested_from_stage || $request->user()?->hasRole('administrator'))
                && $decision->status === 'open',
            403,
        );

        $data = $request->validate([
            'response_note' => ['nullable', 'string', 'max:4000'],
            'specification_action' => ['nullable', Rule::in(['adopt', 'request'])],
            'requested_specifications' => ['nullable', 'array', 'min:1'],
            'requested_specifications.*' => ['required_with:requested_specifications', 'string', 'max:255'],
        ]);

        $details = $decision->details ?? [];
        $responseNote = $data['response_note'] ?? null;

        if ($decision->decision_type === 'specification') {
            if (($data['specification_action'] ?? null) === 'adopt') {
                $details['specification_adopted'] = true;
                $responseNote = '运营部确认采用产品部初步规格。';
            } else {
            abort_unless(! empty($data['requested_specifications']), 422, '请至少填写一条运营部新增的产品规格需求。');

            $requestedSpecifications = collect($data['requested_specifications'])
                ->map(fn (string $specification) => trim($specification))
                ->filter()
                ->unique()
                ->values()
                ->all();
            abort_unless($requestedSpecifications !== [], 422, '请至少填写一条运营部新增的产品规格需求。');

            $details['requested_specifications'] = $requestedSpecifications;
            $responseNote = '运营部新增产品规格：'.collect($requestedSpecifications)
                ->implode('；');
            }
        }

        abort_unless(filled($responseNote), 422, '请填写运营部回复。');
        $decision->update([
            'status' => 'resolved',
            'details' => $details,
            'response_note' => $responseNote,
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
        ]);

        app(RecordProjectActivity::class)->handle($project, $request->user(), 'decision.resolved', [
            'decision_id' => $decision->id,
            'response_note' => $responseNote,
        ]);

        return to_route('projects.index', ['stage' => $request->user()?->department?->code, 'project' => $project]);
    }
}
