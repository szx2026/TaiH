<?php

namespace App\Http\Controllers;

use App\Models\ProductProject;
use Illuminate\Http\JsonResponse;
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

    public function save(Request $request, ProductProject $project): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'products' => ['required', 'array'],
        ]);

        $project->update([
            'profit_data' => [
                'settings' => $validated['settings'],
                'products' => $validated['products'],
                'saved_at' => now()->toIso8601String(),
                'saved_by_name' => $request->user()?->name,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => "已成功保存「{$project->product_name}」的专属利润表",
            'profit_data' => $project->profit_data,
        ]);
    }
}
