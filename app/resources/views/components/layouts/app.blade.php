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
            contain: layout;
            transform: translateZ(0);
            backface-visibility: hidden;
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
<body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        <aside data-app-sidebar class="border-b border-zinc-800 bg-black px-4 py-3 text-zinc-200 lg:border-r lg:border-b-0 lg:border-zinc-200 lg:px-4 lg:py-5">
            <a href="{{ route('dashboard') }}" class="mb-3 flex items-center gap-3 px-2 lg:mb-8">
                <span aria-label="NC 标志" class="grid size-10 place-items-center rounded-xl bg-white shadow-sm">
                    <span aria-hidden="true" class="flex items-baseline gap-px leading-none">
                        <span class="text-[17px] font-black tracking-[-0.08em] text-zinc-950">N</span>
                        <span class="text-[16px] font-semibold tracking-[-0.08em] text-zinc-500">C</span>
                    </span>
                </span>
                <span>
                    <span class="block text-sm font-bold tracking-tight text-white">NC ERP</span>
                    <span class="block text-[11px] text-zinc-400">跨境产品增长协作系统</span>
                </span>
            </a>

            <nav class="space-y-1" aria-label="主导航">
                <x-nav-item label="工作台" :href="route('dashboard')" :active="request()->routeIs('dashboard')" />
                <x-nav-item label="待处理事项" :href="route('feedback.index')" :active="request()->routeIs('feedback.index')" />
                <x-nav-item label="产品部" tone="market_research" :href="route('projects.index', ['stage' => 'market_research'])" :active="request('stage') === 'market_research'" />
                <x-nav-item label="运营部" tone="website_operations" :href="route('projects.index', ['stage' => 'website_operations'])" :active="request('stage') === 'website_operations'" />
                <x-nav-item label="创意部" tone="content_creative" :href="route('projects.index', ['stage' => 'content_creative'])" :active="request('stage') === 'content_creative'" />
                <x-nav-item label="流量部" tone="traffic_growth" :href="route('projects.index', ['stage' => 'traffic_growth'])" :active="request('stage') === 'traffic_growth'" :count="$pendingFeedbackCount" />
                <x-nav-item label="回收站" :href="route('projects.recycle-bin')" :active="request()->routeIs('projects.recycle-bin')" />
            </nav>

            <div class="my-3 border-t border-zinc-800 lg:my-7"></div>
            <p class="sidebar-label">系统管理</p>
            <nav class="space-y-1" aria-label="系统管理">
                <x-nav-item label="成员与权限" :href="route('members.index')" :active="request()->routeIs('members.*')" />
                <x-nav-item label="数据接入" :href="route('integrations.index')" :active="request()->routeIs('integrations.*')" />
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="flex min-h-16 items-center justify-between border-b border-zinc-200 bg-white px-4 sm:px-8">
                <div class="text-sm text-zinc-500">产品从研究、开发到测试的统一协作空间</div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-zinc-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-zinc-500">{{ auth()->user()->department?->name ?? '管理员' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-700 transition hover:border-zinc-400 hover:bg-zinc-50">退出</button>
                    </form>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[1440px] px-4 py-5 sm:px-8 sm:py-8 lg:px-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
