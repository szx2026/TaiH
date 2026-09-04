<?php

namespace App\Http\Controllers;

use App\Models\ProductProject;
use App\Queries\ProjectWorkspaceQuery;
use Illuminate\View\View;

class ProjectWorkspaceController extends Controller
{
    public function show(ProductProject $project, ProjectWorkspaceQuery $workspaceQuery): View
    {
        return view('projects.workspace', ['project' => $workspaceQuery->for($project)]);
    }
}
