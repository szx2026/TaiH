<x-layouts.app title="产品中心 · 跨境产品 ERP">
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">产品中心</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">产品项目池</h1>
            <p class="mt-2 text-sm text-slate-500">统一查看项目进度、负责人和当前协作环节。</p>
        </div>
        <a href="#create-project" class="rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">＋ 新建产品项目</a>
    </div>

    <details id="create-project" class="mb-6 rounded-xl border border-blue-100 bg-blue-50/50 p-5" @if ($errors->any()) open @endif>
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
    </details>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 text-sm font-semibold text-slate-700">全部项目</div>
        <div class="divide-y divide-slate-100">
            @forelse ($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50">
                    <div>
                        <p class="font-semibold">{{ $project->product_name }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $project->project_code }} · {{ $project->market }} · {{ $project->category ?: '未分类' }}</p>
                    </div>
                    <x-status-badge :status="$project->status" />
                </a>
            @empty
                <div class="px-5 py-14 text-center text-sm text-slate-500">还没有产品项目。请从市场研究部创建第一个选品项目。</div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
