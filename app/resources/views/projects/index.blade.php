<x-layouts.app :title="($departmentWorkspace['title'] ?? '产品中心').' · 跨境产品 ERP'">
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">{{ $departmentWorkspace['eyebrow'] ?? '产品中心' }}</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $departmentWorkspace['title'] ?? '产品项目池' }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $departmentWorkspace['description'] ?? '统一查看项目进度、负责人和当前协作环节。' }}</p>
        </div>
        @if(! $departmentWorkspace || $departmentWorkspace['allows_create'])<a href="#create-project" class="rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">＋ 新建产品项目</a>@endif
    </div>

    @if(! $departmentWorkspace || $departmentWorkspace['allows_create'])<details id="create-project" class="mb-6 rounded-xl border border-blue-100 bg-blue-50/50 p-5" @if ($errors->any()) open @endif>
        <summary class="cursor-pointer text-sm font-semibold text-blue-800">手动创建产品项目</summary>
        <form method="POST" action="{{ route('projects.store') }}" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <label class="text-sm font-medium text-slate-700">产品名称
                <input name="product_name" value="{{ old('product_name') }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('product_name') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="text-sm font-medium text-slate-700">产品类目
                <input name="category" value="{{ old('category') }}" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-sm font-medium text-slate-700">目标市场
                <input name="market" value="{{ old('market', 'US') }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-sm font-medium text-slate-700">优先级
                <select name="priority" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="high" @selected(old('priority') === 'high')>高</option>
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>中</option>
                    <option value="low" @selected(old('priority') === 'low')>低</option>
                </select>
            </label>
            <div class="md:col-span-2 xl:col-span-4">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">创建并开始选品研究</button>
            </div>
        </form>
    </details>@endif

    <form method="GET" class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="搜索产品名称或项目编号" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 lg:col-span-2">
        <select name="stage" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">全部环节</option><option value="market_research" @selected(($filters['stage'] ?? '') === 'market_research')>市场研究</option><option value="website_operations" @selected(($filters['stage'] ?? '') === 'website_operations')>网站运营</option><option value="content_creative" @selected(($filters['stage'] ?? '') === 'content_creative')>内容创意</option><option value="traffic_growth" @selected(($filters['stage'] ?? '') === 'traffic_growth')>流量增长</option></select>
        <select name="priority" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">全部优先级</option><option value="high" @selected(($filters['priority'] ?? '') === 'high')>高</option><option value="medium" @selected(($filters['priority'] ?? '') === 'medium')>中</option><option value="low" @selected(($filters['priority'] ?? '') === 'low')>低</option></select>
        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">筛选项目</button>
    </form>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 text-sm font-semibold text-slate-700">{{ $departmentWorkspace['list_label'] ?? '全部项目' }}</div>
        <div class="divide-y divide-slate-100">
            @forelse ($projects as $project)
                <a href="{{ route('projects.index', ['stage' => $filters['stage'] ?? null, 'project' => $project]) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50">
                    <div>
                        <p class="font-semibold">{{ $project->product_name }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $project->project_code }} · {{ $project->market }} · {{ $project->category ?: '未分类' }}</p>
                    </div>
                    <x-status-badge :status="$project->status" />
                </a>
            @empty
                <div class="px-5 py-14 text-center text-sm text-slate-500">{{ $departmentWorkspace ? '当前没有待处理项目。项目推进到本部门后会显示在这里。' : '还没有产品项目。请从市场研究部创建第一个选品项目。' }}</div>
            @endforelse
        </div>
    </section>

    @if($departmentWorkspace && $selectedProject)
        @php($focus = ['market_research' => ['市场研究部重点工作', '选品证据与内部 SKU'], 'website_operations' => ['网站运营部重点工作', '1688 货源、网站产品 SKU 与 Shopify'], 'content_creative' => ['内容创意部重点工作', '素材制作与上传'], 'traffic_growth' => ['流量增长部重点工作', '广告投放与数据复盘']][$filters['stage']] ?? null)
        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-end"><div><p class="text-xs font-semibold text-blue-600">{{ $selectedProject->project_code }}</p><h2 class="mt-1 text-2xl font-bold text-slate-900">{{ $selectedProject->product_name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $selectedProject->market }} · {{ $selectedProject->category ?: '未分类' }} · 负责人：{{ $selectedProject->owner?->name }}</p></div><x-status-badge :status="$selectedProject->status" /></div>
            <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(280px,0.55fr)]"><div class="rounded-xl border border-blue-100 bg-blue-50/50 p-5"><p class="text-sm font-semibold text-blue-900">{{ $focus[0] }}</p><h3 class="mt-2 text-lg font-bold text-slate-900">{{ $focus[1] }}</h3>
                @if(($filters['stage'] ?? '') === 'market_research')<div class="mt-4 grid gap-4 lg:grid-cols-2"><div><p class="text-sm font-semibold">已有选品证据</p><div class="mt-2 space-y-2">@forelse($selectedProject->researchSources as $source)<a href="{{ $source->url }}" class="block rounded-lg bg-white p-3 text-sm text-blue-700">{{ $source->custom_source_name ?: $source->platform }} · {{ $source->evidence_note ?: '查看来源' }}</a>@empty<p class="text-sm text-slate-500">暂无选品证据。</p>@endforelse</div></div><div><p class="text-sm font-semibold">内部 SKU</p><div class="mt-2 space-y-2">@forelse($selectedProject->skus as $sku)<p class="rounded-lg bg-white p-3 text-sm">{{ $sku->sku_code }} · {{ $sku->variant_name }}</p>@empty<p class="text-sm text-slate-500">尚未回填内部 SKU。</p>@endforelse</div></div></div>@else<p class="mt-3 text-sm text-slate-600">该区域将优先展示本部门可处理的数据和操作入口。</p>@endif
            </div><aside class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-slate-900">关联协作摘要</p><dl class="mt-4 space-y-3 text-sm text-slate-600"><div class="flex justify-between gap-3"><dt>详情页</dt><dd>{{ $selectedProject->landingPages->count() }} 个</dd></div><div class="flex justify-between gap-3"><dt>素材</dt><dd>{{ $selectedProject->creativeAssets->count() }} 个</dd></div><div class="flex justify-between gap-3"><dt>投放记录</dt><dd>{{ $selectedProject->campaignTests->count() }} 条</dd></div><div class="flex justify-between gap-3"><dt>待处理反馈</dt><dd>{{ $selectedProject->optimizationFeedback->where('status', '!=', 'resolved')->count() }} 条</dd></div></dl></aside></div>
        </section>
    @endif
</x-layouts.app>
