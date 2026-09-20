<x-layouts.app :title="'每日订单利润核算 · NC ERP'">
@php
    $skusMap = $knownSkus->keyBy('sku_code')->map(function ($s) {
        return [
            'id' => $s->id,
            'sku_code' => $s->sku_code,
            'variant_name' => $s->variant_name,
            'project_name' => $s->project?->product_name ?? '通用产品',
            'project_code' => $s->project?->project_code ?? '',
            'purchase_price' => $s->purchase_price !== null ? (float) $s->purchase_price : null,
            'weight_g' => $s->weight_g !== null ? (float) $s->weight_g : null,
        ];
    });
@endphp

    <style>
        .btn-primary-action {
            background-color: #0f766e !important;
            color: #ffffff !important;
            border: 1px solid #0d9488 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .btn-primary-action:hover {
            background-color: #115e59 !important;
            color: #ffffff !important;
        }
        .btn-secondary-action {
            background-color: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
        }
        .btn-secondary-action:hover {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }
        .field-input {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #1e293b;
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
        }
        .field-input:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }
        .dropzone-active {
            border-color: #0d9488 !important;
            background-color: #f0fdf4 !important;
        }
    </style>

    <!-- 功能切换 Tab -->
    <div class="mb-6 flex items-center border-b border-slate-200">
        <a href="{{ route('profit-calculator.index') }}" class="inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            单品保本盈亏测算
        </a>
        <a href="{{ route('order-profit-calculator.index') }}" class="inline-flex items-center gap-2 border-b-2 border-teal-600 px-4 py-3 text-sm font-bold text-teal-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            每日订单利润核算 (Shopify + FB)
        </a>
    </div>

    <!-- 顶部标题与快捷操作栏 -->
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm font-semibold text-teal-600">
                <span>出海电商核算工具</span>
                <span>·</span>
                <span>Shopify 订单与 Facebook 广告一站式毛利清算</span>
            </div>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">每日订单利润核算</h1>
            <p class="mt-1.5 text-sm text-slate-500">上传 Shopify 当日出单 CSV，自动匹配 ERP 产品采购价与跨境物流，填入 FB 广告花费即时获取净利润、真实 ROAS 与客单价。</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" id="btn-load-sample" class="btn-secondary-action inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold shadow-sm transition cursor-pointer">
                <svg class="h-4 w-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                载入测试示例数据
            </button>
            <button type="button" id="btn-export-csv" class="btn-secondary-action inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer" disabled>
                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                导出核算报表 (CSV)
            </button>
            <button type="button" id="btn-clear-data" class="btn-secondary-action inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition cursor-pointer">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                清空数据
            </button>
        </div>
    </div>

    <!-- 上半部分：左侧 CSV 上传与订单识别，右侧 广告花费与计算参数 -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- 左侧：Shopify CSV 上传卡片 -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2 font-bold text-slate-800 text-base">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">1</span>
                        <span>导入 Shopify 当日订单 (CSV)</span>
                    </div>
                    <span id="upload-badge" class="hidden inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        已成功识别
                    </span>
                </div>

                <!-- 拖拽上传框 -->
                <div id="drop-zone" class="relative rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/70 p-6 text-center transition hover:border-teal-500 hover:bg-teal-50/20 cursor-pointer">
                    <input type="file" id="file-input" accept=".csv,text/csv" class="absolute inset-0 h-full w-full opacity-0 cursor-pointer" />
                    <div class="flex flex-col items-center justify-center pointer-events-none">
                        <svg class="h-10 w-10 text-teal-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-semibold text-slate-700">点击或将 Shopify Orders CSV 拖拽到此处</p>
                        <p class="text-xs text-slate-500 mt-1">支持 Shopify 标准订单导出格式（跨行多商品自动汇总结算）</p>
                    </div>
                </div>

                <!-- 订单筛选与识别概括 -->
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-slate-700">订单状态筛选:</span>
                        <select id="filter-status" class="rounded border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 focus:border-teal-500 focus:outline-none">
                            <option value="paid" selected>仅计算已付款 (paid) 订单</option>
                            <option value="all">计算全部状态订单</option>
                        </select>
                    </div>
                    <div id="file-info-text" class="text-slate-400">暂未上传文件</div>
                </div>
            </div>

            <!-- 数据统计小脚标 -->
            <div id="summary-meta-bar" class="mt-4 rounded-lg bg-slate-50 p-2.5 border border-slate-100 flex items-center justify-around text-xs text-slate-600">
                <div class="text-center">
                    <span class="text-slate-400 block text-[11px]">总订单行数</span>
                    <span id="meta-total-lines" class="font-bold text-slate-700">0</span>
                </div>
                <div class="h-6 w-px bg-slate-200"></div>
                <div class="text-center">
                    <span class="text-slate-400 block text-[11px]">有效订单数</span>
                    <span id="meta-orders-count" class="font-bold text-teal-600">0</span>
                </div>
                <div class="h-6 w-px bg-slate-200"></div>
                <div class="text-center">
                    <span class="text-slate-400 block text-[11px]">售出商品件数</span>
                    <span id="meta-items-count" class="font-bold text-slate-700">0</span>
                </div>
                <div class="h-6 w-px bg-slate-200"></div>
                <div class="text-center">
                    <span class="text-slate-400 block text-[11px]">出单 SKU 种类</span>
                    <span id="meta-skus-count" class="font-bold text-slate-700">0</span>
                </div>
            </div>
        </div>

        <!-- 右侧：广告花费与参数设置卡片 -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2 font-bold text-slate-800 text-base">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">2</span>
                        <span>今日 FB 广告花费与核算参数</span>
                    </div>
                    <span class="text-xs text-slate-400">修改任意项即时重算</span>
                </div>

                <!-- Facebook 广告花费突出输入框 -->
                <div class="rounded-xl border-2 border-teal-500/80 bg-teal-50/30 p-4 mb-4">
                    <label class="block text-xs font-bold text-teal-900 uppercase tracking-wider mb-1">
                        今日 Facebook 广告总花费 (USD $)
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-xl font-bold text-teal-700">$</span>
                        <input type="number" id="input-fb-ad-spend" step="0.01" min="0" placeholder="0.00" value="0.00"
                               class="w-full rounded-lg border border-teal-300 bg-white pl-8 pr-4 py-2.5 text-2xl font-extrabold text-teal-900 placeholder:text-teal-300 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500/20" />
                    </div>
                    <p class="text-[11px] text-teal-700/80 mt-1.5 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        输入 Meta Ads 后台今日的总花费（可直接带小数），系统将用于计算 ROAS 与净利润。
                    </p>
                </div>

                <!-- 成本与汇率参数网格 -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">汇率 (USD➔CNY)</label>
                        <input type="number" id="param-exchange-rate" step="0.01" value="7.20" class="field-input text-xs font-medium py-1.5" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">单单挂号费 (¥)</label>
                        <input type="number" id="param-shipping-base" step="0.5" value="30.00" class="field-input text-xs font-medium py-1.5" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">续重费率 (¥/g)</label>
                        <input type="number" id="param-shipping-rate" step="0.01" value="0.05" class="field-input text-xs font-medium py-1.5" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">网关费率 (%)</label>
                        <input type="number" id="param-payment-fee-rate" step="0.1" value="3.0" class="field-input text-xs font-medium py-1.5" />
                    </div>
                </div>
            </div>

            <!-- 参数说明 -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>跨境物流公式：单均挂号费 + (整单总重 × 续重费率)</span>
                <span>网关扣款：销售额 × 网关费率 + $0.30/单</span>
            </div>
        </div>
    </div>

    <!-- 未匹配 SKU 补齐提示条 (若有未识别到成本的 SKU 时自动显示) -->
    <div id="missing-skus-alert" class="mb-6 hidden rounded-xl border border-amber-300 bg-amber-50 p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h3 class="text-sm font-bold text-amber-800">发现 <span id="missing-skus-count">0</span> 个出单 SKU 在 ERP 中未录入采购成本或重量</h3>
                    <p class="text-xs text-amber-700 mt-0.5">请在下方快速补齐单价与重量，系统将即刻将其计入采购与物流成本：</p>
                </div>
            </div>
        </div>
        <div id="missing-skus-list" class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <!-- 动态生成未匹配 SKU 快捷输入卡片 -->
        </div>
    </div>

    <!-- 核心利润看板 (Summary KPI Dashboard) -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- 卡片 1: 今日净利润 (高亮大卡片) -->
        <div id="card-net-profit" class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm transition">
            <div class="flex items-center justify-between text-xs font-semibold text-emerald-800">
                <span>今日净利润 (Net Profit)</span>
                <span id="badge-profit-status" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">盈利</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight" id="kpi-net-profit-usd">$0.00</span>
                <span class="text-xs text-slate-500" id="kpi-net-profit-cny">≈ ¥0.00</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-emerald-700/80 pt-2 border-t border-emerald-100">
                <span>实际净利率:</span>
                <span id="kpi-net-margin" class="font-bold text-emerald-900 text-sm">0.0%</span>
            </div>
        </div>

        <!-- 卡片 2: 真实 ROAS -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                <span>真实投产比 (ROAS)</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">总营收 ÷ FB花费</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-slate-900" id="kpi-roas">0.00</span>
                <span class="text-sm font-bold text-slate-400">x</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                <span>保本 ROAS 目标:</span>
                <span id="kpi-breakeven-roas" class="font-bold text-slate-700 text-sm">--</span>
            </div>
        </div>

        <!-- 卡片 3: 今日总销售额 -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                <span>今日总销售额 (Revenue)</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Shopify 订单总入账</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-slate-900" id="kpi-gross-revenue">$0.00</span>
                <span class="text-xs text-slate-500" id="kpi-gross-revenue-cny">≈ ¥0.00</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                <span>客单价 (AOV):</span>
                <span id="kpi-aov" class="font-bold text-slate-700 text-sm">$0.00</span>
            </div>
        </div>

        <!-- 卡片 4: 广告获客与出单效率 -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                <span>单均广告成本 (CPA)</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">FB花费 ÷ 订单数</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-slate-900" id="kpi-cpa">$0.00</span>
                <span class="text-xs text-slate-500" id="kpi-order-count-tag">0 笔订单</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                <span>售出总件数:</span>
                <span id="kpi-total-items" class="font-bold text-slate-700 text-sm">0 件</span>
            </div>
        </div>
    </div>

    <!-- 成本深度拆解看板 -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <svg class="h-4 w-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                今日成本结构拆解与占比分析
            </h2>
            <span class="text-xs text-slate-400">总成本 = FB广告 + 产品采购 + 跨境物流 + 平台手续费</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 成本 1: FB 广告花费 -->
            <div class="rounded-lg bg-slate-50 p-4 border border-slate-100">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        Meta 广告花费
                    </span>
                    <span id="cost-percent-ad" class="font-bold text-slate-700">0.0%</span>
                </div>
                <div class="mt-2 text-xl font-bold text-slate-800" id="cost-val-ad">$0.00</div>
                <div class="text-[11px] text-slate-400 mt-1">占比营收: <span id="cost-rev-pct-ad">0.0%</span></div>
            </div>

            <!-- 成本 2: 产品采购成本 -->
            <div class="rounded-lg bg-slate-50 p-4 border border-slate-100">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        产品采购总成本
                    </span>
                    <span id="cost-percent-purchase" class="font-bold text-slate-700">0.0%</span>
                </div>
                <div class="mt-2 text-xl font-bold text-slate-800" id="cost-val-purchase">$0.00</div>
                <div class="text-[11px] text-slate-400 mt-1" id="cost-val-purchase-cny">折合 ¥0.00</div>
            </div>

            <!-- 成本 3: 跨境物流成本 -->
            <div class="rounded-lg bg-slate-50 p-4 border border-slate-100">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                        跨境物流总运费
                    </span>
                    <span id="cost-percent-shipping" class="font-bold text-slate-700">0.0%</span>
                </div>
                <div class="mt-2 text-xl font-bold text-slate-800" id="cost-val-shipping">$0.00</div>
                <div class="text-[11px] text-slate-400 mt-1" id="cost-val-shipping-cny">折合 ¥0.00</div>
            </div>

            <!-- 成本 4: 网关手续费 -->
            <div class="rounded-lg bg-slate-50 p-4 border border-slate-100">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        平台与支付网关费
                    </span>
                    <span id="cost-percent-gateway" class="font-bold text-slate-700">0.0%</span>
                </div>
                <div class="mt-2 text-xl font-bold text-slate-800" id="cost-val-gateway">$0.00</div>
                <div class="text-[11px] text-slate-400 mt-1">费率 3.0% + $0.30/单</div>
            </div>
        </div>
    </div>

    <!-- 表格区域：Tab 标签切换查看【SKU 出单与毛利汇总】vs【订单明细穿透】 -->
    <div class="mb-10 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between border-b border-slate-200 px-6 py-3">
            <div class="flex items-center gap-4">
                <button type="button" id="tab-btn-skus" class="inline-flex items-center gap-1.5 border-b-2 border-teal-600 pb-2 text-sm font-bold text-teal-600 transition cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    出单 SKU 销售与毛利汇总 (<span id="tab-skus-count">0</span>)
                </button>
                <button type="button" id="tab-btn-orders" class="inline-flex items-center gap-1.5 border-b-2 border-transparent pb-2 text-sm font-medium text-slate-500 hover:text-slate-700 transition cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    订单详情穿透列表 (<span id="tab-orders-count">0</span>)
                </button>
            </div>

            <!-- 搜索框 -->
            <div class="w-64">
                <input type="text" id="table-search" placeholder="搜索 SKU / 品名 / 订单号..." class="field-input py-1 text-xs" />
            </div>
        </div>

        <!-- 视图 1: SKU 销售与毛利汇总表 -->
        <div id="view-skus" class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold">SKU 编码</th>
                        <th class="px-4 py-3 font-semibold">关联项目 / 商品名</th>
                        <th class="px-4 py-3 font-semibold text-center">出单件数</th>
                        <th class="px-4 py-3 font-semibold text-right">总销售额 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">单件采购价 (¥)</th>
                        <th class="px-4 py-3 font-semibold text-right">单件重量 (g)</th>
                        <th class="px-4 py-3 font-semibold text-right">总采购成本 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">总物流成本 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">预估毛利 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">毛利率</th>
                    </tr>
                </thead>
                <tbody id="tbody-skus" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            暂无出单数据，请在上方上传 Shopify 订单 CSV 或点击「载入测试示例数据」
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 视图 2: 订单明细穿透表 -->
        <div id="view-orders" class="hidden overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-semibold">订单编号</th>
                        <th class="px-4 py-3 font-semibold">下单时间</th>
                        <th class="px-4 py-3 font-semibold text-center">支付状态</th>
                        <th class="px-4 py-3 font-semibold">包含商品条目</th>
                        <th class="px-4 py-3 font-semibold text-right">订单入账 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">采购成本 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">物流预估 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">网关扣费 ($)</th>
                        <th class="px-4 py-3 font-semibold text-right">单笔订单毛利 ($)</th>
                    </tr>
                </thead>
                <tbody id="tbody-orders" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            暂无订单明细
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 客户端核心计算与 CSV 解析引擎脚本 -->
    <script>
        (function () {
            // 系统已知的 SKU 成本字典
            const erpSkusMap = @json($skusMap);
            
            // 用户在页面临时补齐的未知 SKU 字典: { sku_code: { purchase_price: xx, weight_g: xx } }
            const customOverrides = {};

            // 订单状态与计算状态
            let parsedOrders = [];
            let parsedSkuSummary = [];
            let currentSearchTerm = '';
            let activeTab = 'skus'; // 'skus' | 'orders'

            // DOM 元素引用
            const fileInput = document.getElementById('file-input');
            const dropZone = document.getElementById('drop-zone');
            const uploadBadge = document.getElementById('upload-badge');
            const fileInfoText = document.getElementById('file-info-text');
            const filterStatus = document.getElementById('filter-status');
            const btnLoadSample = document.getElementById('btn-load-sample');
            const btnExportCsv = document.getElementById('btn-export-csv');
            const btnClearData = document.getElementById('btn-clear-data');
            const inputFbAdSpend = document.getElementById('input-fb-ad-spend');

            const paramExchangeRate = document.getElementById('param-exchange-rate');
            const paramShippingBase = document.getElementById('param-shipping-base');
            const paramShippingRate = document.getElementById('param-shipping-rate');
            const paramPaymentFeeRate = document.getElementById('param-payment-fee-rate');

            const missingSkusAlert = document.getElementById('missing-skus-alert');
            const missingSkusCount = document.getElementById('missing-skus-count');
            const missingSkusList = document.getElementById('missing-skus-list');

            const tabBtnSkus = document.getElementById('tab-btn-skus');
            const tabBtnOrders = document.getElementById('tab-btn-orders');
            const viewSkus = document.getElementById('view-skus');
            const viewOrders = document.getElementById('view-orders');
            const tbodySkus = document.getElementById('tbody-skus');
            const tbodyOrders = document.getElementById('tbody-orders');
            const tableSearch = document.getElementById('table-search');

            // 示例测试数据
            const sampleCsv = `Name,Created at,Financial Status,Lineitem quantity,Lineitem name,Lineitem price,Lineitem sku,Total,Shipping
#1081,2026-09-20 09:12:30 +0800,paid,1,Magnetic Wireless Car Charger - Black,29.99,MWC-BLK,29.99,0.00
#1082,2026-09-20 09:45:12 +0800,paid,2,Ultra Slim Power Bank 10000mAh - Silver,39.99,PB-10K-SLV,79.98,0.00
#1083,2026-09-20 10:15:00 +0800,paid,1,RGB Gaming Mouse Pad - XL,19.99,RGB-PAD-XL,24.98,4.99
#1084,2026-09-20 11:20:45 +0800,paid,1,Magnetic Wireless Car Charger - Silver,29.99,MWC-SLV,49.98,0.00
#1084,,,,1,Ultra Slim Power Bank 10000mAh - Black,19.99,PB-10K-BLK,,
#1085,2026-09-20 12:05:18 +0800,paid,3,Braided Fast Charging Cable 2M - Black,9.99,CBL-2M-BLK,29.97,0.00
#1086,2026-09-20 13:40:22 +0800,paid,1,Ultra Slim Power Bank 10000mAh - Silver,39.99,PB-10K-SLV,39.99,0.00
#1087,2026-09-20 14:10:05 +0800,paid,1,Magnetic Wireless Car Charger - Black,29.99,MWC-BLK,29.99,0.00
#1088,2026-09-20 15:30:19 +0800,refunded,1,RGB Gaming Mouse Pad - XL,19.99,RGB-PAD-XL,19.99,0.00
#1089,2026-09-20 16:22:40 +0800,paid,2,Magnetic Wireless Car Charger - Black,29.99,MWC-BLK,59.98,0.00
#1090,2026-09-20 17:05:11 +0800,paid,1,Smart Fitness Tracker Ring - Size 9,49.99,RING-09-BLK,49.99,0.00
#1091,2026-09-20 18:15:33 +0800,paid,1,Ultra Slim Power Bank 10000mAh - Silver,39.99,PB-10K-SLV,39.99,0.00`;

            // RFC 4180 CSV 解析器
            function parseCSV(text) {
                const lines = [];
                let row = [''];
                let inQuotes = false;

                for (let i = 0; i < text.length; i++) {
                    const char = text[i];
                    const nextChar = text[i + 1];

                    if (char === '"') {
                        if (inQuotes && nextChar === '"') {
                            row[row.length - 1] += '"';
                            i++;
                        } else {
                            inQuotes = !inQuotes;
                        }
                    } else if (char === ',' && !inQuotes) {
                        row.push('');
                    } else if ((char === '\r' || char === '\n') && !inQuotes) {
                        if (char === '\r' && nextChar === '\n') {
                            i++;
                        }
                        lines.push(row);
                        row = [''];
                    } else {
                        row[row.length - 1] += char;
                    }
                }
                if (row.length > 1 || (row.length === 1 && row[0] !== '')) {
                    lines.push(row);
                }
                return lines;
            }

            // 处理上传的文件
            function handleFileUpload(file) {
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    const content = e.target.result;
                    processCsvContent(content, file.name);
                };
                reader.readAsText(file, 'UTF-8');
            }

            // 解析 Shopify 订单 CSV 数据
            function processCsvContent(csvString, sourceName) {
                const matrix = parseCSV(csvString);
                if (matrix.length < 2) {
                    alert('上传的 CSV 文件无有效数据或格式不正确');
                    return;
                }

                // 规范化列名
                const rawHeaders = matrix[0].map(h => (h || '').trim().toLowerCase());
                const getIndex = (...keys) => {
                    for (const k of keys) {
                        const idx = rawHeaders.indexOf(k.toLowerCase());
                        if (idx !== -1) return idx;
                    }
                    return -1;
                };

                const idxName = getIndex('name', 'order', 'order number', 'order name');
                const idxCreatedAt = getIndex('created at', 'created_at', 'date');
                const idxFinStatus = getIndex('financial status', 'financial_status', 'payment status', 'status');
                const idxLineQty = getIndex('lineitem quantity', 'line item quantity', 'quantity');
                const idxLineName = getIndex('lineitem name', 'line item name', 'title', 'product name');
                const idxLinePrice = getIndex('lineitem price', 'line item price', 'price');
                const idxLineSku = getIndex('lineitem sku', 'line item sku', 'sku');
                const idxTotal = getIndex('total', 'total amount');
                const idxShipping = getIndex('shipping', 'shipping price');

                if (idxName === -1 || (idxLineSku === -1 && idxLineName === -1)) {
                    alert('未能在 CSV 中识别到 Shopify 标准列名（缺少订单号 Name 或 SKU 列），请确认导出格式是否正确！');
                    return;
                }

                const ordersMap = new Map();
                let totalDataRows = matrix.length - 1;

                for (let i = 1; i < matrix.length; i++) {
                    const row = matrix[i];
                    if (!row || row.length === 0 || (row.length === 1 && !row[0])) continue;

                    const rawName = (row[idxName] || '').trim();
                    const lineSku = idxLineSku !== -1 ? (row[idxLineSku] || '').trim() : '';
                    const lineName = idxLineName !== -1 ? (row[idxLineName] || '').trim() : '';
                    const lineQty = idxLineQty !== -1 ? parseInt(row[idxLineQty], 10) || 0 : 1;
                    const linePrice = idxLinePrice !== -1 ? parseFloat(row[idxLinePrice]) || 0 : 0;

                    // 若行无订单号，可能跟随上一行订单（Shopify 跨行多行项）
                    let targetOrderName = rawName;
                    if (!targetOrderName) {
                        const lastOrder = Array.from(ordersMap.values()).pop();
                        if (lastOrder) {
                            targetOrderName = lastOrder.name;
                        } else {
                            continue;
                        }
                    }

                    if (!ordersMap.has(targetOrderName)) {
                        const createdAt = idxCreatedAt !== -1 ? (row[idxCreatedAt] || '').trim() : '';
                        const finStatus = idxFinStatus !== -1 ? (row[idxFinStatus] || '').trim().toLowerCase() : 'paid';
                        const total = idxTotal !== -1 ? parseFloat(row[idxTotal]) || 0 : 0;
                        const shipping = idxShipping !== -1 ? parseFloat(row[idxShipping]) || 0 : 0;

                        ordersMap.set(targetOrderName, {
                            name: targetOrderName,
                            created_at: createdAt,
                            financial_status: finStatus,
                            total: total,
                            shipping: shipping,
                            items: []
                        });
                    }

                    const orderObj = ordersMap.get(targetOrderName);

                    // 若当前行包含商品项目
                    if (lineSku || lineName || lineQty > 0) {
                        orderObj.items.push({
                            sku: lineSku || 'UNASSIGNED-SKU',
                            name: lineName || '未知商品',
                            quantity: lineQty > 0 ? lineQty : 1,
                            price: linePrice
                        });
                    }
                }

                parsedOrders = Array.from(ordersMap.values());

                // 更新上传状态
                uploadBadge.classList.remove('hidden');
                fileInfoText.textContent = `已加载: ${sourceName || 'Shopify 订单文件'} (${parsedOrders.length} 笔订单)`;
                btnExportCsv.disabled = false;

                // 重新计算并渲染
                calculateAndRender();
            }

            // 获取 SKU 对应的采购价 (¥) 与重量 (g)
            function getSkuSpecs(skuCode) {
                const cleanCode = (skuCode || '').trim();
                
                // 1. 用户临时补齐的
                if (customOverrides[cleanCode]) {
                    return customOverrides[cleanCode];
                }

                // 2. ERP 已录入的精确匹配
                if (erpSkusMap[cleanCode]) {
                    return {
                        purchase_price: erpSkusMap[cleanCode].purchase_price,
                        weight_g: erpSkusMap[cleanCode].weight_g,
                        variant_name: erpSkusMap[cleanCode].variant_name,
                        project_name: erpSkusMap[cleanCode].project_name,
                        is_erp: true
                    };
                }

                // 3. 不区分大小写匹配
                const lowerCode = cleanCode.toLowerCase();
                for (const key in erpSkusMap) {
                    if (key.toLowerCase() === lowerCode) {
                        return {
                            purchase_price: erpSkusMap[key].purchase_price,
                            weight_g: erpSkusMap[key].weight_g,
                            variant_name: erpSkusMap[key].variant_name,
                            project_name: erpSkusMap[key].project_name,
                            is_erp: true
                        };
                    }
                }

                // 4. 未知 SKU，返回默认值
                return {
                    purchase_price: null,
                    weight_g: null,
                    variant_name: '',
                    project_name: '未录入成本',
                    is_erp: false
                };
            }

            // 主计算与渲染函数
            function calculateAndRender() {
                const exchangeRate = parseFloat(paramExchangeRate.value) || 7.20;
                const shippingBaseCny = parseFloat(paramShippingBase.value) || 30.00;
                const shippingRateCny = parseFloat(paramShippingRate.value) || 0.05;
                const paymentFeePct = (parseFloat(paramPaymentFeeRate.value) || 3.0) / 100;
                const paymentFeeFixUsd = 0.30;
                const fbAdSpendUsd = parseFloat(inputFbAdSpend.value) || 0;
                const filterPaidOnly = filterStatus.value === 'paid';

                // 筛选订单
                const validOrders = parsedOrders.filter(ord => {
                    if (!filterPaidOnly) return true;
                    // Shopify paid, partially_refunded, authorized 等
                    return ord.financial_status === 'paid' || ord.financial_status === 'authorized';
                });

                // 统计未知 SKU
                const missingSkus = new Set();
                let totalItemsCount = 0;
                let grossRevenueUsd = 0;
                let totalPurchaseCny = 0;
                let totalShippingCny = 0;
                let totalPaymentFeeUsd = 0;

                // SKU 聚合字典: skuCode -> { qty, revenue, purchaseCny, weightG, name, project }
                const skuMap = new Map();

                // 逐笔订单计算
                const calculatedOrders = validOrders.map(order => {
                    let orderPurchaseCny = 0;
                    let orderWeightG = 0;
                    let orderItemsCount = 0;

                    order.items.forEach(item => {
                        const specs = getSkuSpecs(item.sku);
                        orderItemsCount += item.quantity;

                        // 检查是否有缺失成本
                        if (specs.purchase_price === null || specs.purchase_price === undefined) {
                            missingSkus.add(item.sku);
                        }

                        const pPrice = specs.purchase_price !== null ? specs.purchase_price : 0;
                        const pWeight = specs.weight_g !== null ? specs.weight_g : 100; // 默认 100g 估算

                        const itemPurchaseTotal = pPrice * item.quantity;
                        const itemWeightTotal = pWeight * item.quantity;

                        orderPurchaseCny += itemPurchaseTotal;
                        orderWeightG += itemWeightTotal;

                        // SKU 汇总
                        if (!skuMap.has(item.sku)) {
                            skuMap.set(item.sku, {
                                sku: item.sku,
                                name: item.name || specs.variant_name || '',
                                project_name: specs.project_name || '',
                                purchase_price: pPrice,
                                weight_g: pWeight,
                                total_quantity: 0,
                                total_revenue_usd: 0,
                                total_purchase_cny: 0,
                                is_erp: specs.is_erp || false
                            });
                        }
                        const sEntry = skuMap.get(item.sku);
                        sEntry.total_quantity += item.quantity;
                        sEntry.total_revenue_usd += (item.price * item.quantity);
                        sEntry.total_purchase_cny += itemPurchaseTotal;
                    });

                    totalItemsCount += orderItemsCount;

                    // 订单总金额
                    const orderRevenueUsd = order.total > 0 ? order.total : order.items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
                    grossRevenueUsd += orderRevenueUsd;

                    // 单笔订单跨境物流成本：基础挂号费 + (整单重量 × 续重费率)
                    const orderShippingCny = shippingBaseCny + (orderWeightG * shippingRateCny);
                    totalShippingCny += orderShippingCny;

                    // 单笔网关手续费: 营收 * 费率 + $0.30
                    const orderFeeUsd = (orderRevenueUsd * paymentFeePct) + (orderRevenueUsd > 0 ? paymentFeeFixUsd : 0);
                    totalPaymentFeeUsd += orderFeeUsd;

                    totalPurchaseCny += orderPurchaseCny;

                    // 订单维度数据
                    const orderPurchaseUsd = orderPurchaseCny / exchangeRate;
                    const orderShippingUsd = orderShippingCny / exchangeRate;
                    const orderGrossProfitUsd = orderRevenueUsd - orderPurchaseUsd - orderShippingUsd - orderFeeUsd;

                    return {
                        ...order,
                        revenue_usd: orderRevenueUsd,
                        items_count: orderItemsCount,
                        purchase_usd: orderPurchaseUsd,
                        shipping_usd: orderShippingUsd,
                        gateway_usd: orderFeeUsd,
                        gross_profit_usd: orderGrossProfitUsd
                    };
                });

                // 成本折合 USD
                const totalPurchaseUsd = totalPurchaseCny / exchangeRate;
                const totalShippingUsd = totalShippingCny / exchangeRate;
                const totalCostsUsd = fbAdSpendUsd + totalPurchaseUsd + totalShippingUsd + totalPaymentFeeUsd;
                const netProfitUsd = grossRevenueUsd - totalCostsUsd;
                const netProfitCny = netProfitUsd * exchangeRate;
                const netMarginPct = grossRevenueUsd > 0 ? (netProfitUsd / grossRevenueUsd) * 100 : 0;

                // ROAS 与指标
                const realRoas = fbAdSpendUsd > 0 ? (grossRevenueUsd / fbAdSpendUsd) : 0;
                // 保本 ROAS = 总销售额 / (总销售额 - 采购 - 物流 - 手续费)
                const marginBeforeAd = grossRevenueUsd - totalPurchaseUsd - totalShippingUsd - totalPaymentFeeUsd;
                const breakevenRoas = marginBeforeAd > 0 ? (grossRevenueUsd / marginBeforeAd) : 0;

                const ordersCount = validOrders.length;
                const aovUsd = ordersCount > 0 ? (grossRevenueUsd / ordersCount) : 0;
                const cpaUsd = ordersCount > 0 ? (fbAdSpendUsd / ordersCount) : 0;

                // 更新概括栏
                document.getElementById('meta-total-lines').textContent = parsedOrders.length;
                document.getElementById('meta-orders-count').textContent = ordersCount;
                document.getElementById('meta-items-count').textContent = totalItemsCount;
                document.getElementById('meta-skus-count').textContent = skuMap.size;

                // 更新核心 KPI 看板
                const cardNetProfit = document.getElementById('card-net-profit');
                const badgeProfitStatus = document.getElementById('badge-profit-status');
                const kpiNetProfitUsd = document.getElementById('kpi-net-profit-usd');
                const kpiNetProfitCny = document.getElementById('kpi-net-profit-cny');
                const kpiNetMargin = document.getElementById('kpi-net-margin');

                kpiNetProfitUsd.textContent = (netProfitUsd >= 0 ? '+$' : '-$') + Math.abs(netProfitUsd).toFixed(2);
                kpiNetProfitCny.textContent = '≈ ' + (netProfitCny >= 0 ? '¥' : '-¥') + Math.abs(netProfitCny).toFixed(2);
                kpiNetMargin.textContent = netMarginPct.toFixed(1) + '%';

                if (netProfitUsd >= 0) {
                    cardNetProfit.className = 'rounded-xl border border-emerald-300 bg-emerald-50/60 p-5 shadow-sm transition';
                    badgeProfitStatus.className = 'rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-300';
                    badgeProfitStatus.textContent = '今日盈利';
                    kpiNetProfitUsd.className = 'text-3xl font-black tracking-tight text-emerald-700';
                } else {
                    cardNetProfit.className = 'rounded-xl border border-rose-300 bg-rose-50/60 p-5 shadow-sm transition';
                    badgeProfitStatus.className = 'rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-800 border border-rose-300';
                    badgeProfitStatus.textContent = '今日亏损';
                    kpiNetProfitUsd.className = 'text-3xl font-black tracking-tight text-rose-700';
                }

                document.getElementById('kpi-roas').textContent = realRoas.toFixed(2);
                document.getElementById('kpi-breakeven-roas').textContent = breakevenRoas > 0 ? breakevenRoas.toFixed(2) + 'x' : '--';

                document.getElementById('kpi-gross-revenue').textContent = '$' + grossRevenueUsd.toFixed(2);
                document.getElementById('kpi-gross-revenue-cny').textContent = '≈ ¥' + (grossRevenueUsd * exchangeRate).toFixed(2);
                document.getElementById('kpi-aov').textContent = '$' + aovUsd.toFixed(2);

                document.getElementById('kpi-cpa').textContent = '$' + cpaUsd.toFixed(2);
                document.getElementById('kpi-order-count-tag').textContent = ordersCount + ' 笔有效订单';
                document.getElementById('kpi-total-items').textContent = totalItemsCount + ' 件';

                // 成本拆解更新
                const calcPct = (val, total) => total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0.0%';
                document.getElementById('cost-percent-ad').textContent = calcPct(fbAdSpendUsd, totalCostsUsd);
                document.getElementById('cost-val-ad').textContent = '$' + fbAdSpendUsd.toFixed(2);
                document.getElementById('cost-rev-pct-ad').textContent = calcPct(fbAdSpendUsd, grossRevenueUsd);

                document.getElementById('cost-percent-purchase').textContent = calcPct(totalPurchaseUsd, totalCostsUsd);
                document.getElementById('cost-val-purchase').textContent = '$' + totalPurchaseUsd.toFixed(2);
                document.getElementById('cost-val-purchase-cny').textContent = '折合 ¥' + totalPurchaseCny.toFixed(2);

                document.getElementById('cost-percent-shipping').textContent = calcPct(totalShippingUsd, totalCostsUsd);
                document.getElementById('cost-val-shipping').textContent = '$' + totalShippingUsd.toFixed(2);
                document.getElementById('cost-val-shipping-cny').textContent = '折合 ¥' + totalShippingCny.toFixed(2);

                document.getElementById('cost-percent-gateway').textContent = calcPct(totalPaymentFeeUsd, totalCostsUsd);
                document.getElementById('cost-val-gateway').textContent = '$' + totalPaymentFeeUsd.toFixed(2);

                // 处理未匹配 SKU 提示
                if (missingSkus.size > 0) {
                    missingSkusAlert.classList.remove('hidden');
                    missingSkusCount.textContent = missingSkus.size;
                    renderMissingSkusInputs(Array.from(missingSkus));
                } else {
                    missingSkusAlert.classList.add('hidden');
                }

                // 渲染表格数据
                parsedSkuSummary = Array.from(skuMap.values()).map(s => {
                    const purchaseUsd = s.total_purchase_cny / exchangeRate;
                    // 单个 SKU 物流估算: 单件重量运费 + 均摊单单挂号费
                    const unitShippingCny = shippingBaseCny + (s.weight_g * shippingRateCny);
                    const totalShippingUsd = (unitShippingCny * s.total_quantity) / exchangeRate;
                    const grossProfitUsd = s.total_revenue_usd - purchaseUsd - totalShippingUsd;
                    const marginPct = s.total_revenue_usd > 0 ? (grossProfitUsd / s.total_revenue_usd) * 100 : 0;

                    return {
                        ...s,
                        purchase_usd: purchaseUsd,
                        shipping_usd: totalShippingUsd,
                        gross_profit_usd: grossProfitUsd,
                        margin_pct: marginPct
                    };
                });

                document.getElementById('tab-skus-count').textContent = parsedSkuSummary.length;
                document.getElementById('tab-orders-count').textContent = calculatedOrders.length;

                renderSkuTable(parsedSkuSummary);
                renderOrderTable(calculatedOrders);
            }

            // 渲染未录入成本 SKU 快捷填报卡片
            function renderMissingSkusInputs(skus) {
                missingSkusList.innerHTML = '';
                skus.forEach(sku => {
                    const card = document.createElement('div');
                    card.className = 'rounded-lg border border-amber-200 bg-white p-3 shadow-xs';
                    const currentVal = customOverrides[sku] || { purchase_price: '', weight_g: '' };

                    card.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs font-bold text-slate-800 truncate" title="${sku}">${sku}</span>
                            <span class="text-[10px] rounded bg-amber-100 text-amber-800 px-1.5 py-0.5">待补齐</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-500 mb-0.5">采购价 (¥)</label>
                                <input type="number" step="0.1" placeholder="0.00" value="${currentVal.purchase_price ?? ''}"
                                       data-sku="${sku}" data-field="purchase_price"
                                       class="missing-sku-input field-input py-1 text-xs" />
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-500 mb-0.5">重量 (g)</label>
                                <input type="number" step="1" placeholder="100" value="${currentVal.weight_g ?? ''}"
                                       data-sku="${sku}" data-field="weight_g"
                                       class="missing-sku-input field-input py-1 text-xs" />
                            </div>
                        </div>
                    `;
                    missingSkusList.appendChild(card);
                });

                // 绑定输入事件
                missingSkusList.querySelectorAll('.missing-sku-input').forEach(input => {
                    input.addEventListener('input', function () {
                        const sku = this.dataset.sku;
                        const field = this.dataset.field;
                        const val = parseFloat(this.value);

                        if (!customOverrides[sku]) {
                            customOverrides[sku] = { purchase_price: null, weight_g: null };
                        }
                        customOverrides[sku][field] = isNaN(val) ? null : val;

                        // 即刻重算
                        calculateAndRender();
                    });
                });
            }

            // 渲染 SKU 汇总表
            function renderSkuTable(list) {
                const term = currentSearchTerm.toLowerCase();
                const filtered = list.filter(item => {
                    return item.sku.toLowerCase().includes(term) ||
                           item.name.toLowerCase().includes(term) ||
                           item.project_name.toLowerCase().includes(term);
                });

                if (filtered.length === 0) {
                    tbodySkus.innerHTML = `<tr><td colspan="10" class="py-8 text-center text-slate-400">没有找到匹配的 SKU 数据</td></tr>`;
                    return;
                }

                // 按出单件数倒序排列
                filtered.sort((a, b) => b.total_quantity - a.total_quantity);

                tbodySkus.innerHTML = filtered.map(item => {
                    const isProfit = item.gross_profit_usd >= 0;
                    return `
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-800">
                                ${item.sku}
                                ${!item.is_erp ? '<span class="ml-1 text-[10px] text-amber-600 bg-amber-50 px-1 rounded border border-amber-200">自定义</span>' : ''}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 truncate max-w-xs" title="${item.name}">${item.name || '--'}</div>
                                <div class="text-[11px] text-slate-400">${item.project_name || '未关联项目'}</div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-teal-700">${item.total_quantity}</td>
                            <td class="px-4 py-3 text-right font-medium text-slate-800">$${item.total_revenue_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right text-slate-600">¥${item.purchase_price ? item.purchase_price.toFixed(2) : '0.00'}</td>
                            <td class="px-4 py-3 text-right text-slate-600">${item.weight_g || 0}g</td>
                            <td class="px-4 py-3 text-right text-slate-600">$${item.purchase_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right text-slate-600">$${item.shipping_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right font-bold ${isProfit ? 'text-emerald-600' : 'text-rose-600'}">
                                ${isProfit ? '+' : ''}$${item.gross_profit_usd.toFixed(2)}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold ${isProfit ? 'text-emerald-700' : 'text-rose-700'}">
                                ${item.margin_pct.toFixed(1)}%
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // 渲染订单穿透列表
            function renderOrderTable(list) {
                const term = currentSearchTerm.toLowerCase();
                const filtered = list.filter(ord => {
                    if (ord.name.toLowerCase().includes(term)) return true;
                    return ord.items.some(it => it.sku.toLowerCase().includes(term) || it.name.toLowerCase().includes(term));
                });

                if (filtered.length === 0) {
                    tbodyOrders.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-slate-400">没有找到匹配的订单</td></tr>`;
                    return;
                }

                tbodyOrders.innerHTML = filtered.map(ord => {
                    const isProfit = ord.gross_profit_usd >= 0;
                    const itemsDesc = ord.items.map(i => `${i.sku} × ${i.quantity}`).join(', ');

                    return `
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-mono font-bold text-teal-700">${ord.name}</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">${ord.created_at || '--'}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block rounded px-2 py-0.5 text-[10px] font-semibold uppercase ${ord.financial_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'}">
                                    ${ord.financial_status}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700 max-w-sm truncate" title="${itemsDesc}">
                                <span class="font-medium">${ord.items_count} 件商品:</span> ${itemsDesc}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">$${ord.revenue_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right text-slate-600">$${ord.purchase_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right text-slate-600">$${ord.shipping_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right text-slate-500">$${ord.gateway_usd.toFixed(2)}</td>
                            <td class="px-4 py-3 text-right font-bold ${isProfit ? 'text-emerald-600' : 'text-rose-600'}">
                                ${isProfit ? '+' : ''}$${ord.gross_profit_usd.toFixed(2)}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // 导出 CSV 功能
            function exportCsvReport() {
                if (!parsedSkuSummary || parsedSkuSummary.length === 0) {
                    alert('当前无有效核算数据可导出');
                    return;
                }

                let csv = '\uFEFF'; // UTF-8 BOM
                csv += 'SKU,商品名称,关联项目,出单数量,销售额(USD),单件采购价(CNY),单件重量(g),采购总成本(USD),物流预估(USD),预估毛利(USD),毛利率\n';

                parsedSkuSummary.forEach(row => {
                    const line = [
                        `"${row.sku.replace(/"/g, '""')}"`,
                        `"${(row.name || '').replace(/"/g, '""')}"`,
                        `"${(row.project_name || '').replace(/"/g, '""')}"`,
                        row.total_quantity,
                        row.total_revenue_usd.toFixed(2),
                        (row.purchase_price || 0).toFixed(2),
                        row.weight_g || 0,
                        row.purchase_usd.toFixed(2),
                        row.shipping_usd.toFixed(2),
                        row.gross_profit_usd.toFixed(2),
                        row.margin_pct.toFixed(1) + '%'
                    ].join(',');
                    csv += line + '\n';
                });

                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `订单利润核算明细_${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }

            // 交互事件监听
            fileInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    handleFileUpload(e.target.files[0]);
                }
            });

            // 拖拽高亮
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('dropzone-active');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('dropzone-active');
                });
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files[0]) {
                    handleFileUpload(dt.files[0]);
                }
            });

            btnLoadSample.addEventListener('click', function () {
                processCsvContent(sampleCsv, 'Shopify_Sample_Orders.csv');
                inputFbAdSpend.value = '85.50';
                calculateAndRender();
            });

            btnClearData.addEventListener('click', function () {
                parsedOrders = [];
                parsedSkuSummary = [];
                fileInput.value = '';
                uploadBadge.classList.add('hidden');
                fileInfoText.textContent = '暂未上传文件';
                inputFbAdSpend.value = '0.00';
                btnExportCsv.disabled = true;
                calculateAndRender();
            });

            btnExportCsv.addEventListener('click', exportCsvReport);

            // 参数输入监听实时重算
            [inputFbAdSpend, paramExchangeRate, paramShippingBase, paramShippingRate, paramPaymentFeeRate].forEach(el => {
                el.addEventListener('input', calculateAndRender);
            });

            filterStatus.addEventListener('change', calculateAndRender);

            // 搜索过滤
            tableSearch.addEventListener('input', function (e) {
                currentSearchTerm = e.target.value.trim();
                renderSkuTable(parsedSkuSummary);
                renderOrderTable(parsedOrders);
            });

            // 表格视图 Tab 切换
            tabBtnSkus.addEventListener('click', function () {
                activeTab = 'skus';
                tabBtnSkus.className = 'inline-flex items-center gap-1.5 border-b-2 border-teal-600 pb-2 text-sm font-bold text-teal-600 transition cursor-pointer';
                tabBtnOrders.className = 'inline-flex items-center gap-1.5 border-b-2 border-transparent pb-2 text-sm font-medium text-slate-500 hover:text-slate-700 transition cursor-pointer';
                viewSkus.classList.remove('hidden');
                viewOrders.classList.add('hidden');
            });

            tabBtnOrders.addEventListener('click', function () {
                activeTab = 'orders';
                tabBtnOrders.className = 'inline-flex items-center gap-1.5 border-b-2 border-teal-600 pb-2 text-sm font-bold text-teal-600 transition cursor-pointer';
                tabBtnSkus.className = 'inline-flex items-center gap-1.5 border-b-2 border-transparent pb-2 text-sm font-medium text-slate-500 hover:text-slate-700 transition cursor-pointer';
                viewOrders.classList.remove('hidden');
                viewSkus.classList.add('hidden');
            });
        })();
    </script>
</x-layouts.app>
