<x-layouts.app title="回收站 · NC ERP">
    @php($canManage = auth()->user()?->department?->code === 'traffic_growth' || auth()->user()?->hasRole('administrator'))
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-rose-600">项目回收站</p>
            <h1 class="mt-1 text-3xl font-bold">已归档产品项目</h1>
            <p class="mt-2 text-sm text-slate-500">归档项目不会删除业务资料；流量部可恢复项目，或彻底永久删除项目。</p>
        </div>
        @if($canManage && $projects->isNotEmpty())
            <form method="POST" action="{{ route('projects.recycle-bin.empty') }}" onsubmit="return confirm('确定要清空回收站吗？所有已归档项目及其全部关联数据都将被永久删除，且无法恢复！');">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition">清空回收站</button>
            </form>
        @endif
    </div>
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">
            @forelse($projects as $project)
                <div class="flex flex-col justify-between gap-3 p-5 sm:flex-row sm:items-center">
                    <div>
                        <p class="font-semibold">{{ $project->product_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $project->project_code }} · {{ $project->market }} · 已归档</p>
                    </div>
                    @if($canManage)
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('projects.restore', $project) }}">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">恢复产品项目</button>
                            </form>
                            <form method="POST" action="{{ route('projects.force-destroy', $project) }}" onsubmit="return confirm('确定要永久删除“{{ $project->product_name }}”吗？此操作将彻底清除所有业务资料且无法恢复！');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">永久删除</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center text-sm text-slate-500">回收站为空。</div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
