<x-layouts.app title="待处理事项 · NC ERP">
    <div class="mb-7">
        <p class="text-sm font-semibold text-black">协作中心</p>
        <h1 class="mt-1 text-3xl font-bold">全部待处理事项</h1>
        <p class="mt-2 text-sm text-slate-500">汇总当前部门需要跟进的反馈、协作请求和规格任务。</p>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">
            @forelse($items as $item)
                <article class="p-5 sm:flex sm:items-start sm:justify-between sm:gap-6">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $item['type'] }}</span>
                            <span class="text-xs text-slate-400">{{ $item['created_at']->format('Y-m-d H:i') }}</span>
                        </div>
                        <h2 class="mt-3 text-lg font-bold text-slate-950">{{ $item['project']->product_name }}</h2>
                        <p class="mt-1 font-medium text-slate-800">{{ $item['title'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['description'] }}</p>
                    </div>
                    <a href="{{ route('projects.index', ['stage' => $item['stage'] ?: $stage, 'project' => $item['project']->id]) }}" class="mt-4 inline-flex shrink-0 items-center rounded-lg bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 sm:mt-0">去处理</a>
                </article>
            @empty
                <p class="px-5 py-14 text-center text-sm text-slate-500">当前没有待处理事项。</p>
            @endforelse
        </div>
    </section>
</x-layouts.app>
