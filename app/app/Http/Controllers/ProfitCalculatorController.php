<?php

namespace App\Http\Controllers;

use App\Models\ProductProject;
use App\Models\ProductSku;
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

    public function ordersIndex(Request $request): View
    {
        $skus = ProductSku::query()
            ->with('project:id,product_name,project_code')
            ->whereNotNull('sku_code')
            ->orderBy('id')
            ->get(['id', 'product_project_id', 'variant_name', 'sku_code', 'purchase_price', 'weight_g']);

        return view('profit-calculator.orders', [
            'knownSkus' => $skus,
        ]);
    }

    public function saveNew(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'settings' => ['required', 'array'],
            'products' => ['required', 'array'],
        ]);

        $user = $request->user();
        $departmentId = $user?->department_id ?? \App\Models\Department::first()?->id ?? 1;

        // 生成专属测算项目编号 PC-年月-序号
        $datePrefix = 'PC-' . now()->format('Ym') . '-';
        $lastProject = ProductProject::where('project_code', 'like', $datePrefix . '%')
            ->orderByDesc('id')
            ->first();
        $seq = 1;
        if ($lastProject && preg_match('/-(\d+)$/', $lastProject->project_code, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }
        $projectCode = $datePrefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        $project = ProductProject::create([
            'project_code' => $projectCode,
            'product_name' => $validated['product_name'],
            'market' => 'US',
            'priority' => 'initial_screening',
            'current_stage' => 'market_research',
            'status' => 'draft',
            'owner_department_id' => $departmentId,
            'owner_user_id' => $user?->id ?? 1,
            'created_by' => $user?->id ?? 1,
            'released_at' => now(),
            'profit_data' => [
                'settings' => $validated['settings'],
                'products' => $validated['products'],
                'saved_at' => now()->toIso8601String(),
                'saved_by_name' => $user?->name,
            ],
        ]);

        $syncedSkusCount = $this->syncProductSkus($project, $validated['products'], $user?->id);

        return response()->json([
            'success' => true,
            'message' => "已成功创建「{$project->product_name}」利润表，并沉淀 {$syncedSkusCount} 个公司内部 SKU！",
            'project' => [
                'id' => $project->id,
                'name' => $project->product_name,
                'code' => $project->project_code,
                'skus' => $project->skus()->get()->map(fn ($s) => [
                    'id' => $s->id,
                    'variant_name' => $s->variant_name,
                    'sku_code' => $s->sku_code,
                    'purchase_price' => $s->purchase_price !== null ? (float) $s->purchase_price : null,
                    'weight_g' => $s->weight_g !== null ? (float) $s->weight_g : null,
                ])->values(),
                'profit_data' => $project->profit_data,
                'save_url' => route('profit-calculator.save', $project),
            ],
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

        $syncedSkusCount = $this->syncProductSkus($project, $validated['products'], $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "已成功保存「{$project->product_name}」专属利润表（已同步 {$syncedSkusCount} 个公司 SKU）",
            'profit_data' => $project->profit_data,
        ]);
    }

    protected function syncProductSkus(ProductProject $project, array $products, ?int $userId): int
    {
        $count = 0;
        foreach ($products as $item) {
            $skuCode = trim($item['sku'] ?? '');
            if ($skuCode === '') {
                continue;
            }
            $cost = (isset($item['costCny']) && $item['costCny'] !== '' && is_numeric($item['costCny']))
                ? (float) $item['costCny']
                : null;
            $weight = (isset($item['weightG']) && $item['weightG'] !== '' && is_numeric($item['weightG']))
                ? (float) $item['weightG']
                : null;

            $sku = ProductSku::where('product_project_id', $project->id)
                ->where('sku_code', $skuCode)
                ->first();

            if ($sku) {
                $sku->update([
                    'purchase_price' => $cost ?? $sku->purchase_price,
                    'weight_g' => $weight ?? $sku->weight_g,
                ]);
            } else {
                ProductSku::create([
                    'product_project_id' => $project->id,
                    'sku_code' => $skuCode,
                    'variant_name' => $skuCode,
                    'sku_status' => 'imported',
                    'purchase_price' => $cost,
                    'weight_g' => $weight,
                    'created_by' => $userId ?? 1,
                ]);
            }
            $count++;
        }
        return $count;
    }
}
