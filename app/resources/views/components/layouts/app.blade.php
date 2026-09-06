<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '跨境产品 ERP' }}</title>
    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        <aside class="border-r border-slate-200 bg-slate-950 px-4 py-5 text-slate-200">
            <a href="{{ route('dashboard') }}" class="mb-8 flex items-center gap-3 px-2">
                <span class="grid size-9 place-items-center rounded-xl bg-blue-500 text-sm font-black text-white">N</span>
                <span>
                    <span class="block text-sm font-bold tracking-tight text-white">NORTHSTAR ERP</span>
                    <span class="block text-[11px] text-slate-400">跨境产品增长协作系统</span>
                </span>
            </a>

            <nav class="space-y-1" aria-label="主导航">
                <x-nav-item label="工作台" :href="route('dashboard')" :active="request()->routeIs('dashboard')" />
                <x-nav-item label="产品部" :href="route('projects.index', ['stage' => 'market_research'])" :active="request('stage') === 'market_research'" />
                <x-nav-item label="运营部" :href="route('projects.index', ['stage' => 'website_operations'])" :active="request('stage') === 'website_operations'" />
                <x-nav-item label="创意部" :href="route('projects.index', ['stage' => 'content_creative'])" :active="request('stage') === 'content_creative'" />
                <x-nav-item label="流量部" :href="route('projects.index', ['stage' => 'traffic_growth'])" :active="request('stage') === 'traffic_growth'" />
                <x-nav-item label="反馈中心" :href="route('feedback.index')" :active="request()->routeIs('feedback.*')" :count="$pendingFeedbackCount" />
                <x-nav-item label="回收站" :href="route('projects.recycle-bin')" :active="request()->routeIs('projects.recycle-bin')" />
            </nav>

            <div class="my-7 border-t border-slate-800"></div>
            <p class="sidebar-label">系统管理</p>
            <nav class="space-y-1" aria-label="系统管理">
                <x-nav-item label="成员与权限" :href="route('members.index')" :active="request()->routeIs('members.*')" />
                <x-nav-item label="数据接入" :href="route('integrations.index')" :active="request()->routeIs('integrations.*')" />
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-5 sm:px-8">
                <div class="text-sm text-slate-500">产品从研究、开发到测试的统一协作空间</div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()->department?->name ?? '管理员' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">退出</button>
                    </form>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[1440px] px-5 py-8 sm:px-8 lg:px-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
