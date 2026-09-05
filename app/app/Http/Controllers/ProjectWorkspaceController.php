<?php

namespace App\Http\Controllers;

use App\Models\ProductProject;
use App\Support\ProjectStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectWorkspaceController extends Controller
{
    public function show(Request $request, ProductProject $project): RedirectResponse
    {
        $requestedStage = $request->string('return_stage')->value();
        $stage = in_array($requestedStage, ProjectStage::ordered(), true)
            ? $requestedStage
            : $project->current_stage;

        return to_route('projects.index', ['stage' => $stage, 'project' => $project]);
    }
}
