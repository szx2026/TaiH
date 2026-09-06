<x-layouts.app title="工作台 · 跨境产品 ERP">
    <div class="mb-7">
        <p class="text-sm font-semibold text-blue-600">工作台</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $isAdministrator ? '全局工作看板' : '我的工作看板' }}</h1>
        <p class="mt-2 text-sm text-slate-500">聚焦当前环节的产品任务、优化反馈与投放表现。</p>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="当前项目" :value="$projects->count()" hint="当前部门待协作" />
        <x-metric-card label="待处理反馈" :value="$feedback->count()" hint="未解决或处理中" />
        <x-metric-card label="广告花费" :value="'$'.number_format((float) $metrics->spend, 2)" hint="当前可见项目" />
        <x-metric-card label="平均点击率" :value="$metrics->ctr.'%'" hint="点击 / 展示" />
    </section>

    <section class="mt-7 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-800">四部门项目总览</h2>
        <p class="mt-1 text-sm text-slate-500">汇总全部未归档产品项目的协作产出。</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-sm">
            <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">产品部</p><p class="mt-1 text-slate-600">选品证据 {{ $collaborationOverview->sum('research_sources_count') }} 条 · SKU {{ $collaborationOverview->sum('skus_count') }} 个</p></div>
            <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">运营部</p><p class="mt-1 text-slate-600">1688 货源 {{ $collaborationOverview->sum('sources_count') }} 条 · Shopify {{ $collaborationOverview->sum('landing_pages_count') }} 页</p></div>
            <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">创意部</p><p class="mt-1 text-slate-600">素材 {{ $collaborationOverview->sum('creative_assets_count') }} 个</p></div>
            <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">流量部</p><p class="mt-1 text-slate-600">投放 {{ $collaborationOverview->sum('campaign_tests_count') }} 条 · 待处理反馈 {{ $feedback->count() }} 条</p></div>
        </div>
    </section>

    <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.85fr)]">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800">当前环节项目</h2>
                <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-blue-600">查看全部</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    <a href="{{ route('projects.index', ['stage' => $project->current_stage, 'project' => $project]) }}" class="block px-5 py-4 transition hover:bg-slate-50">
                        <div class="flex items-start justify-between gap-4"><div><p class="font-semibold">{{ $project->product_name }}</p><p class="mt-1 text-xs text-slate-500">{{ $project->project_code }} · {{ $project->market }} · {{ $project->owner?->name }}</p></div><x-status-badge :status="$project->status" /></div>
                        <div class="mt-3"><x-stage-rail :stage="$project->current_stage" /></div>
                    </a>
                @empty
                    <p class="px-5 py-12 text-center text-sm text-slate-500">当前没有需要处理的项目。</p>
                @endforelse
            </div>
        </section>

        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-sm font-semibold text-slate-800">待处理优化反馈</h2></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($feedback as $item)
                        <div class="px-5 py-4"><p class="text-sm font-semibold">{{ $item->project->product_name }}</p><p class="mt-1 text-sm text-slate-600">{{ $item->note }}</p><p class="mt-2 text-xs text-slate-400">目标：{{ $item->target_stage }}</p></div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-slate-500">当前没有待处理反馈。</p>
                    @endforelse
                </div>
            </section>
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="text-sm font-semibold text-slate-800">最近活动</h2></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        <div class="px-5 py-3"><p class="text-sm text-slate-700">{{ $activity->event }}</p><p class="mt-1 text-xs text-slate-400">{{ $activity->project->product_name }} · {{ $activity->actor->name }} · {{ $activity->created_at->diffForHumans() }}</p></div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-slate-500">项目活动会显示在这里。</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
