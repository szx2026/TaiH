<?php

namespace App\Http\Controllers;

use App\Models\ProductProject;
use App\Queries\ProjectWorkspaceQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectWorkspaceController extends Controller
{
    public function show(Request $request, ProductProject $project, ProjectWorkspaceQuery $workspaceQuery): View
    {
        $tab = $request->string('tab', 'overview')->value();
        abort_unless(in_array($tab, ['overview', 'research', 'operations', 'assets', 'campaigns', 'feedback'], true), 404);

        return view('projects.workspace', ['project' => $workspaceQuery->for($project), 'tab' => $tab]);
    }
}
