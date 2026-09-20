<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'NC ERP' }}</title>
    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
    <style id="nc-system-motion">
        :root {
            --ease-silky: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --dur-instant: 140ms;
            --dur-snappy: 200ms;
            --dur-gentle: 280ms;
        }

        html {
            scroll-behavior: smooth;
            scrollbar-gutter: stable;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-y: scroll;
            overflow-anchor: auto;
        }

        body {
            font-feature-settings: "tnum" 1, "cv02" 1, "cv03" 1, "cv04" 1;
            font-variant-numeric: tabular-nums;
            text-rendering: optimizeLegibility;
        }

        /* Universal Box Model & Media Stability */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        img, video {
            max-width: 100%;
            height: auto;
            vertical-align: middle;
            font-style: italic;
            shape-margin: 0.75rem;
        }

        img:not([height]) {
            min-height: 1px;
        }

        /* Layout Containment for Render Stability */
        main {
            contain: layout style;
            will-change: opacity, transform;
            animation: silkyFadeIn var(--dur-gentle) var(--ease-silky) both;
        }

        [data-app-sidebar] {
            contain: layout style;
        }

        .project-summary-header {
            display: grid !important;
            gap: 1.5rem !important;
            grid-template-columns: minmax(0, 1.35fr) minmax(20rem, 0.85fr) !important;
            align-items: start !important;
            contain: layout;
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        @media (max-width: 1080px) {
            .project-summary-header {
                grid-template-columns: 1fr !important;
                gap: 1.25rem !important;
            }
        }

        [data-workspace-refresh-result] {
            min-height: 1.15rem;
            line-height: 1.15rem;
            display: block;
        }

        @keyframes silkyFadeIn {
            0% {
                opacity: 0;
                transform: translateY(6px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 2. Interactive Buttons & Action Controls */
        button,
        input[type="submit"],
        input[type="button"],
        .btn-smooth,
        a.btn-smooth {
            transition: transform var(--dur-instant) var(--ease-silky),
                        background-color var(--dur-snappy) var(--ease-silky),
                        border-color var(--dur-snappy) var(--ease-silky),
                        box-shadow var(--dur-snappy) var(--ease-silky),
                        color var(--dur-instant) var(--ease-silky),
                        opacity var(--dur-instant) var(--ease-silky);
            will-change: transform;
        }

        button:hover:not(:disabled),
        input[type="submit"]:hover:not(:disabled),
        input[type="button"]:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        button:active:not(:disabled),
        input[type="submit"]:active:not(:disabled),
        input[type="button"]:active:not(:disabled) {
            transform: scale(0.976) translateY(0);
        }

        /* 3. Form Inputs, Selects & Textareas Smooth Focus */
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]),
        select,
        textarea {
            transition: border-color var(--dur-instant) var(--ease-silky),
                        box-shadow var(--dur-snappy) var(--ease-silky),
                        background-color var(--dur-instant) var(--ease-silky) !important;
        }

        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):focus,
        select:focus,
        textarea:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08) !important;
            border-color: #0f172a !important;
        }

        /* 4. Interactive Cards, Project Items & Metric Cards */
        .project-card-item,
        .interactive-card,
        .metric-card-smooth,
        .shared-department-card {
            transition: transform var(--dur-snappy) var(--ease-silky),
                        box-shadow var(--dur-snappy) var(--ease-silky),
                        border-color var(--dur-snappy) var(--ease-silky),
                        background-color var(--dur-snappy) var(--ease-silky);
            will-change: transform;
        }

        .project-card-item:hover,
        .metric-card-smooth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -3px rgba(15, 23, 42, 0.06), 0 3px 6px -2px rgba(15, 23, 42, 0.04);
        }

        .project-card-item:active {
            transform: translateY(0) scale(0.99);
        }

        /* 5. Sidebar Navigation Transitions */
        [data-app-sidebar] nav a {
            transition: transform var(--dur-instant) var(--ease-silky),
                        background-color var(--dur-snappy) var(--ease-silky),
                        color var(--dur-instant) var(--ease-silky),
                        box-shadow var(--dur-snappy) var(--ease-silky) !important;
        }

        [data-app-sidebar] nav a:hover {
            transform: translateX(3px);
        }

        [data-app-sidebar] nav a:active {
            transform: translateX(1px) scale(0.99);
        }

        /* 6. Modal Backdrop & Dialog Spring Entrance */
        .modal-backdrop-smooth {
            opacity: 0;
            transition: opacity var(--dur-snappy) var(--ease-silky);
            pointer-events: none;
        }

        .modal-backdrop-smooth.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-dialog-smooth {
            transform: scale(0.94) translateY(12px);
            opacity: 0;
            transition: transform var(--dur-gentle) var(--ease-silky),
                        opacity var(--dur-snappy) var(--ease-silky);
        }

        .modal-backdrop-smooth.is-open .modal-dialog-smooth {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* 7. Progress Dot Micro-interaction */
        .project-progress-dot {
            transition: transform var(--dur-snappy) var(--ease-spring),
                        background-color var(--dur-instant) var(--ease-silky),
                        border-color var(--dur-instant) var(--ease-silky),
                        box-shadow var(--dur-snappy) var(--ease-silky) !important;
        }

        .project-progress-step:hover .project-progress-dot {
            transform: scale(1.15);
        }

        .project-progress-step:active .project-progress-dot {
            transform: scale(0.95);
        }

        /* 8. Table Rows & Divide Lists */
        .divide-y > * {
            transition: background-color var(--dur-instant) var(--ease-silky);
        }

        /* 9. Respect Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            button:hover, 
            input[type="submit"]:hover,
            [data-app-sidebar] nav a:hover,
            .project-card-item:hover,
            .metric-card-smooth:hover {
                transform: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside data-app-sidebar class="border-b lg:border-b-0 lg:border-r px-3.5 py-4 lg:py-6 flex flex-col justify-between" style="background-color: #0b0f19; border-color: rgba(255, 255, 255, 0.08);">
            <div>
                <a href="{{ route('dashboard') }}" class="mb-5 flex items-center gap-2.5 px-2 lg:mb-6">
                    <span aria-label="NC 标志" class="grid size-8 place-items-center rounded-lg bg-gradient-to-br from-teal-400 to-emerald-500 shadow-sm shadow-teal-950/40 text-slate-950 font-black text-sm tracking-tight">
                        NC
                    </span>
                    <div>
                        <div class="flex items-center gap-1.5 leading-tight">
                            <span class="text-sm font-bold tracking-tight text-white">NC ERP</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">Pro</span>
                        </div>
                        <span class="block text-[11px] text-slate-400">跨境协作增长系统</span>
                    </div>
                </a>

                <!-- 1. 业务概览 -->
                <div class="mb-4">
                    <p class="px-2 mb-1.5 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">业务概览</p>
                    <nav class="space-y-0.5" aria-label="业务概览">
                        <x-nav-item label="工作台" :href="route('dashboard')" :active="request()->routeIs('dashboard')" />
                        <x-nav-item label="待处理事项" :href="route('feedback.index')" :active="request()->routeIs('feedback.index')" />
                    </nav>
                </div>

                <!-- 2. 业务部门 -->
                <div class="mb-4">
                    <p class="px-2 mb-1.5 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">业务部门</p>
                    <nav class="space-y-0.5" aria-label="业务部门">
                        <x-nav-item label="产品部" tone="market_research" :href="route('projects.index', ['stage' => 'market_research'])" :active="request('stage') === 'market_research'" />
                        <x-nav-item label="运营部" tone="website_operations" :href="route('projects.index', ['stage' => 'website_operations'])" :active="request('stage') === 'website_operations'" />
                        <x-nav-item label="创意部" tone="content_creative" :href="route('projects.index', ['stage' => 'content_creative'])" :active="request('stage') === 'content_creative'" />
                        <x-nav-item label="流量部" tone="traffic_growth" :href="route('projects.index', ['stage' => 'traffic_growth'])" :active="request('stage') === 'traffic_growth'" :count="$pendingFeedbackCount" />
                    </nav>
                </div>

                <!-- 3. 利润与核算 -->
                <div class="mb-4">
                    <p class="px-2 mb-1.5 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">利润与核算</p>
                    <nav class="space-y-0.5" aria-label="利润核算">
                        <x-nav-item label="单品保本测算" :href="route('profit-calculator.index')" :active="request()->routeIs('profit-calculator.*')" />
                        <x-nav-item label="每日订单核算" :href="route('order-profit-calculator.index')" :active="request()->routeIs('order-profit-calculator.*')" />
                    </nav>
                </div>

                <!-- 4. 系统管理 -->
                <div class="mb-2">
                    <p class="px-2 mb-1.5 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">系统管理</p>
                    <nav class="space-y-0.5" aria-label="系统管理">
                        <x-nav-item label="成员与权限" :href="route('members.index')" :active="request()->routeIs('members.*')" />
                        <x-nav-item label="数据接入" :href="route('integrations.index')" :active="request()->routeIs('integrations.*')" />
                        <x-nav-item label="回收站" :href="route('projects.recycle-bin')" :active="request()->routeIs('projects.recycle-bin')" />
                    </nav>
                </div>
            </div>

            <div class="hidden lg:block pt-3 mt-4 border-t border-white/[0.06] px-2 text-[11px] text-slate-400">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">NC Global</span>
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]"></span>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex flex-col">
            <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-8">
                @php
                    $breadcrumb = ['parent' => '业务概览', 'current' => '工作台看板'];
                    if (request()->routeIs('dashboard')) {
                        $breadcrumb = ['parent' => '业务概览', 'current' => '工作台看板'];
                    } elseif (request()->routeIs('feedback.*')) {
                        $breadcrumb = ['parent' => '业务概览', 'current' => '待处理事项'];
                    } elseif (request()->routeIs('projects.recycle-bin')) {
                        $breadcrumb = ['parent' => '系统管理', 'current' => '回收站'];
                    } elseif (request()->routeIs('projects.*')) {
                        $stageNames = [
                            'market_research' => '产品部 · 市场研究',
                            'website_operations' => '运营部 · 独立站运营',
                            'content_creative' => '创意部 · 素材制作',
                            'traffic_growth' => '流量部 · 广告投放',
                        ];
                        $stage = request('stage');
                        $stageName = $stageNames[$stage] ?? '项目流转总览';
                        $breadcrumb = ['parent' => '业务部门', 'current' => $stageName];
                    } elseif (request()->routeIs('profit-calculator.*')) {
                        $breadcrumb = ['parent' => '利润与核算', 'current' => '单品保本测算'];
                    } elseif (request()->routeIs('order-profit-calculator.*')) {
                        $breadcrumb = ['parent' => '利润与核算', 'current' => '每日订单利润核算'];
                    } elseif (request()->routeIs('members.*')) {
                        $breadcrumb = ['parent' => '系统管理', 'current' => '成员与权限'];
                    } elseif (request()->routeIs('integrations.*')) {
                        $breadcrumb = ['parent' => '系统管理', 'current' => '数据接入'];
                    }
                @endphp
                <nav class="flex items-center gap-2 text-xs" aria-label="页面路径">
                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-400">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        {{ $breadcrumb['parent'] }}
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-semibold text-slate-800">{{ $breadcrumb['current'] }}</span>
                </nav>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="grid size-8 place-items-center rounded-full bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </span>
                        <div class="hidden text-left sm:block leading-tight">
                            <p class="text-xs font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200/80">
                                {{ auth()->user()->department?->name ?? '管理员' }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-1">
                        @csrf
                        <button type="submit" title="退出登录" class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-xs">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>退出</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[1440px] px-4 py-5 sm:px-8 sm:py-8 lg:px-10 flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
