<?php

namespace App\Http\Controllers;

use App\Models\ProductProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitCalculatorController extends Controller
{
    public function index(Request $request): View
    {
        $projects = ProductProject::query()
            ->where('status', '!=', 'archived')
            ->with(['skus' => fn ($query) => $query->orderBy('id')])
            ->latest('released_at')
            ->get();

        $selectedProjectId = $request->query('project');
        $preloadedProject = null;

        if ($selectedProjectId) {
            $preloadedProject = $projects->firstWhere('id', (int) $selectedProjectId);
        }

        return view('profit-calculator.index', [
            'projects' => $projects,
            'preloadedProject' => $preloadedProject,
        ]);
    }
}
