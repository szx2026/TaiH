<x-layouts.app title="工作台 · NC ERP">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $isAdministrator ? '全局工作看板' : '我的工作看板' }}</h1>
            <p class="mt-1 text-xs text-slate-500">统一聚合四部门项目协作产出、跨部门待办与投放表现。</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profit-calculator.index') }}" class="btn-smooth inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 shadow-xs">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                利润测算
            </a>
            <a href="{{ route('order-profit-calculator.index') }}" class="btn-smooth inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-teal-700 shadow-xs">
                <svg class="w-3.5 h-3.5 text-teal-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                订单核算
            </a>
        </div>
    </div>

    <section class="grid gap-3.5 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="当前项目" :value="$projects->count()" hint="当前部门待协作" />
        <x-metric-card label="待处理反馈" :value="$feedback->count()" hint="未解决或处理中" />
        <x-metric-card label="广告花费" :value="'$'.number_format((float) $metrics->spend, 2)" hint="当前可见项目" />
        <x-metric-card label="平均点击率" :value="$metrics->ctr.'%'" hint="点击 / 展示" />
    </section>

    <section class="mt-6 rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">四部门项目总览</h2>
                <p class="mt-0.5 text-xs text-slate-500">汇总全部未归档产品项目的跨部门协作产出。</p>
            </div>
            <span class="text-[11px] font-medium text-slate-400">实时联动</span>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-xs">
            <div class="rounded-lg border border-orange-100 bg-orange-50/40 p-3.5 transition hover:border-orange-200">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-orange-900">产品部</span>
                    <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                </div>
                <p class="mt-2 text-slate-600 leading-relaxed">选品证据 <span class="font-semibold text-slate-900">{{ $collaborationOverview->sum('research_sources_count') }}</span> 条 · SKU <span class="font-semibold text-slate-900">{{ $collaborationOverview->sum('skus_count') }}</span> 个</p>
            </div>
            <div class="rounded-lg border border-sky-100 bg-sky-50/40 p-3.5 transition hover:border-sky-200">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sky-900">运营部</span>
                    <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                </div>
                <p class="mt-2 text-slate-600 leading-relaxed">1688货源 <span class="font-semibold text-slate-900">{{ $collaborationOverview->sum('sources_count') }}</span> 条 · 落地页 <span class="font-semibold text-slate-900">{{ $collaborationOverview->sum('landing_pages_count') }}</span> 个</p>
            </div>
            <div class="rounded-lg border border-amber-100 bg-amber-50/40 p-3.5 transition hover:border-amber-200">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-900">创意部</span>
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                </div>
                <p class="mt-2 text-slate-600 leading-relaxed">素材产出 <span class="font-semibold text-slate-900">{{ $collaborationOverview->sum('creative_assets_count') }}</span> 份</p>
            </div>
            <div class="rounded-lg border border-violet-100 bg-violet-50/40 p-3.5 transition hover:border-violet-200">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-violet-900">流量部</span>
                    <span class="h-2 w-2 rounded-full bg-violet-400"></span>
                </div>
                <p class="mt-2 text-slate-600 leading-relaxed">广告投放 <span class="font-semibold text-slate-900">{{ $collaborationOverview->sum('campaign_tests_count') }}</span> 条 · 待办 <span class="font-semibold text-slate-900">{{ $feedback->count() }}</span> 条</p>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.85fr)]">
        <section class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                <h2 class="text-sm font-bold text-slate-900">当前环节项目</h2>
                <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 transition">查看全部 →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    <a href="{{ route('projects.index', ['stage' => $project->current_stage, 'project' => $project]) }}" class="block px-5 py-3.5 transition hover:bg-slate-50/80">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $project->product_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $project->project_code }} · {{ $project->market }} · {{ $project->owner?->name }}</p>
                            </div>
                            <x-status-badge :status="$project->status" />
                        </div>
                        <div class="mt-2.5"><x-stage-rail :stage="$project->current_stage" /></div>
                    </a>
                @empty
                    <p class="px-5 py-10 text-center text-xs text-slate-400">当前没有需要处理的项目。</p>
                @endforelse
            </div>
        </section>

        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-900">待处理优化反馈</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($feedback as $item)
                        <div class="px-5 py-3.5">
                            <p class="text-xs font-bold text-slate-900">{{ $item->project->product_name }}</p>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $item->note }}</p>
                            <p class="mt-1.5 text-[11px] text-slate-400">目标：{{ $stageLabels[$item->target_stage] ?? $item->target_stage }}</p>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-xs text-slate-400">当前没有待处理反馈。</p>
                    @endforelse
                </div>
            </section>
            <section class="rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-3.5">
                    <h2 class="text-sm font-bold text-slate-900">最近活动</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        <div class="px-5 py-3">
                            <p class="text-xs text-slate-700 leading-snug">{{ $activity->event }}</p>
                            <p class="mt-1 text-[11px] text-slate-400">{{ $activity->project->product_name }} · {{ $activity->actor->name }} · {{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-xs text-slate-400">暂无活动记录。</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
