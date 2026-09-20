<x-layouts.app :title="'产品盈亏计算工具 · NC ERP'">
@php
    $projectsPayload = $projects->mapWithKeys(function ($proj) {
        return [
            $proj->id => [
                'id' => $proj->id,
                'name' => $proj->product_name,
                'code' => $proj->project_code,
                'skus' => $proj->skus->map(fn ($s) => [
                    'id' => $s->id,
                    'variant_name' => $s->variant_name,
                    'sku_code' => $s->sku_code,
                    'purchase_price' => $s->purchase_price !== null ? (float) $s->purchase_price : null,
                    'weight_g' => $s->weight_g !== null ? (float) $s->weight_g : null,
                ])->values(),
                'profit_data' => $proj->profit_data,
                'save_url' => route('profit-calculator.save', $proj),
            ]
        ];
    });
@endphp

    <!-- 功能切换 Tab -->
    <div class="mb-6 flex items-center border-b border-slate-200">
        <a href="{{ route('profit-calculator.index') }}" class="inline-flex items-center gap-2 border-b-2 border-teal-600 px-4 py-3 text-sm font-bold text-teal-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            单品保本盈亏测算
        </a>
        <a href="{{ route('order-profit-calculator.index') }}" class="inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            每日订单利润核算 (Shopify + FB)
        </a>
    </div>

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-teal-600">实时测算 · 支持绑定项目保存与自由独立计算</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">产品盈亏计算工具</h1>
            <p class="mt-2 text-sm text-slate-500">自动带入系统产品规格已知信息，填写售价实时计算保本广告成本（CPR）与盈亏保本 ROI。</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700 border border-teal-200">
                <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                草稿实时缓存
            </span>
        </div>
    </div>

    <!-- ERP 项目联动与保存控制台 -->
    <section class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[280px]">
                <div class="flex items-center gap-2 text-slate-700 font-semibold text-sm">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <span>选择产品项目：</span>
                </div>
                <div class="flex-1 max-w-xl">
                    <select id="erp-project-select" class="field-input mt-0 text-sm w-full font-medium">
                        <option value="">-- 自由独立计算（不关联任何项目） --</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" @selected($preloadedProject?->id === $proj->id)>
                                {{ $proj->product_name }} ({{ $proj->skus->count() }} 个规格) · {{ $proj->project_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div id="mode-badge-container">
                    <span id="mode-badge" class="mode-status-badge free">
                        <span class="dot"></span>
                        自由独立计算模式
                    </span>
                </div>

                <button type="button" id="btn-save-project-profit" class="btn-primary-action" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    <span id="btn-save-text">保存当前产品利润表</span>
                </button>
            </div>
        </div>

        <div id="erp-mode-desc" class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-2.5 text-xs text-slate-500">
            <span id="erp-mode-hint">💡 自由独立计算模式：不关联系统项目，数据保存在浏览器本地，可任意自由测算。</span>
            <span id="erp-last-saved" class="text-slate-400 font-medium"></span>
        </div>
    </section>

    <!-- 状态提示 -->
    <p id="status-message" class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-800 border border-blue-200" aria-live="polite" hidden></p>

    <!-- 计算参数设置 -->
    <section id="settings" class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="settings-title"></section>

    <!-- 汇总结果看板 -->
    <section id="summary" class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm" aria-live="polite" aria-labelledby="summary-title"></section>

    <!-- 产品明细表 -->
    <section id="products" class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="products-title"></section>

    <!-- 数据管理 -->
    <section id="data-actions" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="data-title"></section>

    <input id="backup-file" type="file" accept="application/json,.json" hidden>

    <style>
        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff !important;
            background-color: #0f766e !important;
            border: 1px solid #0f766e !important;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-primary-action:hover:not(:disabled) {
            background-color: #115e59 !important;
            border-color: #115e59 !important;
        }
        .btn-primary-action:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            background-color: #0f766e !important;
        }
        .btn-primary-action svg {
            color: #ffffff !important;
            stroke: #ffffff !important;
        }

        .btn-secondary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.45rem 0.9rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-secondary-action:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
        }

        .mode-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .mode-status-badge.free {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #cbd5e1 !important;
        }
        .mode-status-badge.saved {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border: 1px solid #6ee7b7 !important;
        }
        .mode-status-badge.unsaved {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border: 1px solid #fcd34d !important;
        }
        .mode-status-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }
        .mode-status-badge.free .dot { background-color: #94a3b8; }
        .mode-status-badge.saved .dot { background-color: #10b981; }
        .mode-status-badge.unsaved .dot { background-color: #f59e0b; }

        .settings-grid { display: grid; grid-template-columns: repeat(5, minmax(130px, 1fr)); gap: 1rem; }
        .settings-grid label { display: grid; gap: 0.35rem; color: #0f172a; font-weight: 600; font-size: 0.875rem; }
        .settings-grid .field-note { color: #64748b; font-size: 0.75rem; font-weight: 400; }
        .settings-grid input { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.65rem; color: #0f172a; background: white; font-size: 0.875rem; }
        .settings-grid input:focus-visible { outline: 2px solid #0f766e; outline-offset: 1px; }

        .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; }
        .metric-card { padding: 1rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .metric-card strong { display: block; margin-top: 0.25rem; font-size: 1.25rem; font-weight: 700; color: #0f172a; }
        .metric-card .metric-label { color: #64748b; font-size: 0.8125rem; }
        .metric-card.highlight { background: #f0fdfa; border-color: #99f6e4; }
        .metric-card.highlight strong { color: #0d9488; }
        .metric-card.loss { background: #fff1f2; border-color: #fecdd3; }
        .metric-card.loss strong { color: #e11d48; }

        .calc-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .calc-table th { padding: 0.65rem 0.5rem; text-align: left; color: #64748b; font-size: 0.75rem; font-weight: 600; white-space: nowrap; border-bottom: 1px solid #e2e8f0; }
        .calc-table td { padding: 0.65rem 0.5rem; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
        .calc-table input { width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.4rem 0.55rem; font-size: 0.875rem; color: #0f172a; background: white; }
        .calc-table input:focus { border-color: #0d9488; outline: none; }
        .calc-table .input-sku { min-width: 140px; }

        .result-box { min-width: 170px; font-size: 0.8125rem; padding: 0.25rem 0.5rem; border-radius: 6px; }
        .result-box strong { display: block; font-weight: 700; }
        .result-box.valid { background: #f0fdfa; color: #0f766e; }
        .result-box.valid strong { color: #0d9488; }
        .result-box.loss { background: #fff1f2; color: #be123c; }
        .result-box.loss strong { color: #e11d48; }
        .result-box.incomplete { background: #f8fafc; color: #64748b; }
        .result-box.incomplete strong { color: #64748b; }
        .result-box.invalid { background: #fff1f2; color: #9f1239; }
        .result-box.invalid strong { color: #e11d48; }
        .result-box small { display: block; color: inherit; opacity: 0.85; margin-top: 0.2rem; font-size: 0.75rem; line-height: 1.35; }

        .row-action-btns { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .btn-copy { padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; background: white; border: 1px solid #cbd5e1; border-radius: 6px; color: #334155; cursor: pointer; }
        .btn-copy:hover { background: #f1f5f9; }
        .btn-delete { padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; background: white; border: 1px solid #fca5a5; border-radius: 6px; color: #dc2626; cursor: pointer; }
        .btn-delete:hover { background: #fef2f2; }

        @media (max-width: 980px) {
            .settings-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 760px) {
            .settings-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .calc-table, .calc-table tbody, .calc-table tr, .calc-table td { display: block; width: 100%; }
            .calc-table thead { display: none; }
            .calc-table tr { margin: 0.8rem 0; padding: 0.5rem 0.8rem; border: 1px solid #e2e8f0; border-radius: 10px; background: white; }
            .calc-table td { display: grid; grid-template-columns: minmax(7rem, 40%) 1fr; align-items: center; gap: 0.7rem; padding: 0.45rem 0; border-top: 1px solid #f1f5f9; }
            .calc-table td:first-child { border-top: 0; }
            .calc-table td::before { content: attr(data-label); color: #64748b; font-size: 0.75rem; font-weight: 600; }
            .calc-table td[data-label="计算结果"] { display: block; }
            .calc-table td[data-label="计算结果"]::before { display: block; margin-bottom: 0.35rem; }
            .calc-table td[data-label="操作"] { display: block; }
            .calc-table td[data-label="操作"]::before { display: block; margin-bottom: 0.35rem; }
        }
        @media (max-width: 430px) {
            .settings-grid, .summary-grid { grid-template-columns: 1fr; }
        }
    </style>

    <script>
(function (root, factory) {
  const api = factory();
  if (typeof module === 'object' && module.exports) module.exports = api;
  root.CalculatorCore = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  const DEFAULT_SETTINGS = Object.freeze({
    exchangeRate: 6.65, feeRate: 0.075, refundRate: 0.05,
    logisticsBaseCny: 23, logisticsRateCnyKg: 58,
  });

  function blankProduct(index = 0) {
    return { id: `product-${Date.now()}-${index}`, sku: '', costCny: '',
      weightG: '', priceUsd: '', storeShippingUsd: '' };
  }

  function validateSettings(settings) {
    const errors = [];
    const isFiniteSetting = value => value !== '' && value !== null && value !== undefined
      && Number.isFinite(Number(value));
    if (!(isFiniteSetting(settings?.exchangeRate) && Number(settings.exchangeRate) > 0))
      errors.push('汇率必须大于 0');
    if (!(isFiniteSetting(settings?.feeRate)
      && Number(settings.feeRate) >= 0 && Number(settings.feeRate) <= 1))
      errors.push('手续费率必须在 0% 到 100% 之间');
    if (!(isFiniteSetting(settings?.refundRate)
      && Number(settings.refundRate) >= 0 && Number(settings.refundRate) <= 1))
      errors.push('退款率必须在 0% 到 100% 之间');
    if (!(isFiniteSetting(settings?.logisticsBaseCny) && Number(settings.logisticsBaseCny) >= 0))
      errors.push('物流基础费不能为负数');
    if (!(isFiniteSetting(settings?.logisticsRateCnyKg) && Number(settings.logisticsRateCnyKg) >= 0))
      errors.push('物流单价不能为负数');
    return errors;
  }

  const isBlank = value => value === '' || value === null || value === undefined;

  function calculateProduct(product, settings) {
    const required = [
      ['costCny', '产品成本'], ['weightG', '重量'], ['priceUsd', '售价'],
    ];
    const missingFields = required.filter(([key]) => isBlank(product[key]))
      .map(([, label]) => label);
    if (missingFields.length) return { id: product.id, status: 'incomplete', missingFields };
    const values = {
      costCny: Number(product.costCny), weightG: Number(product.weightG),
      priceUsd: Number(product.priceUsd),
      storeShippingUsd: isBlank(product.storeShippingUsd) ? 0 : Number(product.storeShippingUsd),
    };
    const invalidFields = Object.entries(values)
      .filter(([, value]) => !Number.isFinite(value) || value < 0).map(([key]) => key);
    const settingErrors = validateSettings(settings);
    if (invalidFields.length || settingErrors.length) {
      return { id: product.id, status: 'invalid', invalidFields, errors: settingErrors };
    }
    const exchangeRate = Number(settings.exchangeRate);
    const productCostUsd = values.costCny / exchangeRate;
    const logisticsUsd = (Number(settings.logisticsBaseCny)
      + values.weightG / 1000 * Number(settings.logisticsRateCnyKg)) / exchangeRate;
    const totalRevenueUsd = values.priceUsd + values.storeShippingUsd;
    const feeUsd = totalRevenueUsd * Number(settings.feeRate);
    const refundUsd = totalRevenueUsd * Number(settings.refundRate);
    const breakEvenCprUsd = totalRevenueUsd - productCostUsd - logisticsUsd - feeUsd - refundUsd;
    const result = { id: product.id, status: breakEvenCprUsd > 0 ? 'valid' : 'loss',
      missingFields: [], productCostUsd, logisticsUsd, totalRevenueUsd,
      feeUsd, refundUsd, breakEvenCprUsd, roi: null };
    if (breakEvenCprUsd > 0) result.roi = totalRevenueUsd / breakEvenCprUsd;
    return result;
  }

  function calculateSummary(products, settings) {
    const included = products.map(product => calculateProduct(product, settings))
      .filter(result => result.status === 'valid' || result.status === 'loss');
    const sum = key => included.reduce((total, result) => total + result[key], 0);
    const summary = { productCount: included.length,
      totalRevenueUsd: sum('totalRevenueUsd'), productCostUsd: sum('productCostUsd'),
      logisticsUsd: sum('logisticsUsd'), feeUsd: sum('feeUsd'), refundUsd: sum('refundUsd'),
      breakEvenCprUsd: sum('breakEvenCprUsd'), roi: null };
    if (summary.breakEvenCprUsd > 0) {
      summary.roi = summary.totalRevenueUsd / summary.breakEvenCprUsd;
    }
    return summary;
  }

  function formatMoney(value) {
    return Number.isFinite(value) ? `${value.toFixed(2)}` : '—';
  }

  return { DEFAULT_SETTINGS, blankProduct, validateSettings,
    calculateProduct, calculateSummary, formatMoney };
});

(function (root, factory) {
  const api = factory(root.CalculatorCore);
  if (typeof module === 'object' && module.exports) module.exports = api;
  root.StateManager = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function (Core) {
  const STORAGE_KEY = 'nc-erp-profit-calc-free-v1';

  function createInitialState() {
    return { version: 1, settings: { ...Core.DEFAULT_SETTINGS },
      products: Array.from({ length: 5 }, (_, i) => Core.blankProduct(i)) };
  }

  function createStateFromSkus(skus) {
    const products = [];
    const seenPrices = new Set();

    (skus || []).forEach((sku, idx) => {
      const hasPrice = sku.purchase_price !== null && sku.purchase_price !== undefined && sku.purchase_price !== '';
      const cost = hasPrice ? Number(sku.purchase_price) : '';

      // 同一价格的 SKU 只自动填充一个
      const priceKey = hasPrice ? String(cost) : `__empty__${idx}`;
      if (hasPrice) {
        if (seenPrices.has(priceKey)) {
          return;
        }
        seenPrices.add(priceKey);
      }

      const label = sku.sku_code
        ? (sku.variant_name ? `${sku.variant_name} (${sku.sku_code})` : sku.sku_code)
        : (sku.variant_name || `规格 ${idx + 1}`);

      products.push({
        id: `erp-sku-${sku.id || Date.now()}-${idx}`,
        sku: label,
        costCny: cost,
        weightG: sku.weight_g !== null && sku.weight_g !== undefined && sku.weight_g !== '' ? Number(sku.weight_g) : '',
        priceUsd: '',
        storeShippingUsd: 0,
      });
    });

    // 若规格不足 5 个，补足至 5 行，方便用户直接录入
    while (products.length < 5) {
      products.push(Core.blankProduct(products.length));
    }

    return {
      version: 1,
      settings: { ...Core.DEFAULT_SETTINGS },
      products,
    };
  }

  function normalizeState(value) {
    if (!value || value.version !== 1) throw new Error('不支持的数据版本');
    if (!value.settings || typeof value.settings !== 'object' || Array.isArray(value.settings))
      throw new Error('参数格式无效');
    if (!Array.isArray(value.products) || value.products.length < 1)
      throw new Error('产品明细列表不能为空');

    const settings = { ...Core.DEFAULT_SETTINGS, ...value.settings };
    const settingErrors = Core.validateSettings(settings);
    if (settingErrors.length) throw new Error(settingErrors[0]);
    for (const key of Object.keys(Core.DEFAULT_SETTINGS)) settings[key] = Number(settings[key]);

    const numericKeys = ['costCny', 'weightG', 'priceUsd', 'storeShippingUsd'];
    const productIds = new Set();
    const products = value.products.map((product, index) => {
      if (!product || typeof product !== 'object' || Array.isArray(product))
        throw new Error(`第 ${index + 1} 个产品格式无效`);
      const fallback = Core.blankProduct(index);
      const normalized = { ...fallback, ...product,
        id: String(product.id || fallback.id), sku: String(product.sku || '') };
      if (productIds.has(normalized.id)) {
        normalized.id = `${normalized.id}-${index}`;
      }
      productIds.add(normalized.id);
      for (const key of numericKeys) {
        if (normalized[key] === '' || normalized[key] === null || normalized[key] === undefined) {
          normalized[key] = '';
        } else if (!Number.isFinite(Number(normalized[key])) || Number(normalized[key]) < 0) {
          throw new Error(`第 ${index + 1} 个产品包含无效数值`);
        } else {
          normalized[key] = Number(normalized[key]);
        }
      }
      return normalized;
    });
    return { version: 1, settings, products };
  }

  function importBackup(text) {
    let value;
    try { value = JSON.parse(text); } catch { throw new Error('备份文件不是有效的 JSON'); }
    return normalizeState(value);
  }

  function exportBackup(state) { return JSON.stringify(normalizeState(state), null, 2); }

  function loadState(storage, key = STORAGE_KEY) {
    try {
      const raw = storage ? storage.getItem(key) : null;
      return raw ? importBackup(raw) : createInitialState();
    } catch {
      return createInitialState();
    }
  }

  function saveState(storage, state, key = STORAGE_KEY) {
    if (storage) {
      try {
        storage.setItem(key, exportBackup(state));
      } catch (e) {
        console.warn('Storage save failed', e);
      }
    }
  }

  return { STORAGE_KEY, createInitialState, createStateFromSkus, normalizeState,
    loadState, saveState, exportBackup, importBackup };
});

const PROJECTS_DATA = @json($projectsPayload);
const INITIAL_PROJECT_ID = {{ $preloadedProject ? $preloadedProject->id : 'null' }};

let state;
let statusMessage = '';
let storage = null;
let currentProjectId = null;

function getActiveStorageKey() {
  return currentProjectId ? `nc-erp-profit-calc-proj-${currentProjectId}` : StateManager.STORAGE_KEY;
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>'"]/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;',
  }[char]));
}

function formatDateTime(isoString) {
  if (!isoString) return '';
  try {
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return '';
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
  } catch {
    return '';
  }
}

function showStatus(message) {
  statusMessage = message;
  const node = document.getElementById('status-message');
  if (node) {
    node.textContent = message;
    node.hidden = !message;
  }
}

function replaceState(candidate, successMessage) {
  try {
    const normalized = StateManager.normalizeState(candidate);
    StateManager.saveState(storage, normalized, getActiveStorageKey());
    state = normalized;
    statusMessage = successMessage;
  } catch (error) {
    statusMessage = `存储失败，当前数据未更改：${error.message}`;
  }
  render();
}

function updateAndRender(mutator, structureChanged = false) {
  mutator(state);
  try {
    StateManager.normalizeState(state);
  } catch (error) {
    statusMessage = `当前输入有误，尚未保存：${error.message}`;
    if (structureChanged) render(); else renderCalculations();
    return;
  }
  try {
    StateManager.saveState(storage, state, getActiveStorageKey());
    statusMessage = '';
  } catch {
    statusMessage = '浏览器存储不可用或空间不足。更改仍保留在当前页面，但尚未保存。';
  }
  if (structureChanged) render(); else renderCalculations();
}

function formatRoi(value) {
  return Number.isFinite(value) ? value.toFixed(2) : '—';
}

function renderSettings(errors) {
  const settings = state.settings;
  const fields = [
    ['exchangeRate', '汇率', '1 美元兑换人民币', settings.exchangeRate],
    ['feeRate', '手续费率', '输入百分比', settings.feeRate === '' ? '' : Number(settings.feeRate) * 100],
    ['refundRate', '退款率', '输入百分比', settings.refundRate === '' ? '' : Number(settings.refundRate) * 100],
    ['logisticsBaseCny', '物流基础费', '人民币 ¥', settings.logisticsBaseCny],
    ['logisticsRateCnyKg', '物流单价', '人民币 ¥ / kg', settings.logisticsRateCnyKg],
  ];
  const errorByField = Object.fromEntries(fields.map(([key, label]) => [key,
    errors.find(error => error.startsWith(label))]));
  document.getElementById('settings').innerHTML = `
    <h2 id="settings-title" class="text-base font-bold text-slate-900 mb-3">计算参数设置</h2>
    <div class="settings-grid">${fields.map(([key, label, note, value]) => `
      <label>${label}<span class="field-note">${note}</span>
        <input data-setting="${key}" type="number" step="any" inputmode="decimal" value="${escapeHtml(value)}" aria-label="${label}" ${errorByField[key] ? `aria-invalid="true" aria-describedby="settings-${key}-error"` : ''}>
      </label>`).join('')}</div>
    <div id="settings-errors" aria-live="polite">${errors.length ? `<div class="mt-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-800 border border-rose-200"><strong>请修正以下参数：</strong><ul class="list-disc pl-5 mt-1">${errors.map(error => {
      const key = fields.find(([, label]) => error.startsWith(label))?.[0];
      return `<li${key ? ` id="settings-${key}-error"` : ''}>${escapeHtml(error)}</li>`;
    }).join('')}</ul></div>` : ''}</div>`;
}

function updateInputError(input, message, errorId) {
  if (!input || typeof input.setAttribute !== 'function') return;
  if (message) {
    input.setAttribute('aria-invalid', 'true');
    input.setAttribute('aria-describedby', errorId);
  } else {
    input.removeAttribute('aria-invalid');
    input.removeAttribute('aria-describedby');
  }
}

function renderSettingsErrors(errors) {
  const fields = [
    ['exchangeRate', '汇率'], ['feeRate', '手续费率'], ['refundRate', '退款率'],
    ['logisticsBaseCny', '物流基础费'], ['logisticsRateCnyKg', '物流单价'],
  ];
  const errorByField = Object.fromEntries(fields.map(([key, label]) => [key,
    errors.find(error => error.startsWith(label))]));
  const node = document.getElementById('settings-errors');
  if (node) node.innerHTML = errors.length
    ? `<div class="mt-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-800 border border-rose-200"><strong>请修正以下参数：</strong><ul class="list-disc pl-5 mt-1">${errors.map(error => {
      const key = fields.find(([, label]) => error.startsWith(label))?.[0];
      return `<li${key ? ` id="settings-${key}-error"` : ''}>${escapeHtml(error)}</li>`;
    }).join('')}</ul></div>` : '';
  for (const input of document.querySelectorAll('input[data-setting]')) {
    const message = errorByField[input.dataset.setting];
    updateInputError(input, message, `settings-${input.dataset.setting}-error`);
  }
}

function renderSummary(summary, settingsErrors) {
  const unavailable = settingsErrors.length > 0;
  const display = value => unavailable ? '—' : `$${CalculatorCore.formatMoney(value)}`;
  const roi = unavailable || summary.roi === null ? '—' : formatRoi(summary.roi);
  const resultClass = unavailable || summary.breakEvenCprUsd <= 0 ? 'loss' : 'highlight';
  document.getElementById('summary').innerHTML = `
    <h2 id="summary-title" class="text-base font-bold text-slate-900 mb-3">汇总测算指标</h2>
    <div class="summary-grid">
      <div class="metric-card"><span class="metric-label">已纳入产品规格</span><strong>${unavailable ? '—' : summary.productCount} 个</strong></div>
      <div class="metric-card"><span class="metric-label">总预计收入</span><strong>${display(summary.totalRevenueUsd)}</strong></div>
      <div class="metric-card"><span class="metric-label">产品采购总成本</span><strong>${display(summary.productCostUsd)}</strong></div>
      <div class="metric-card"><span class="metric-label">跨境物流总成本</span><strong>${display(summary.logisticsUsd)}</strong></div>
      <div class="metric-card"><span class="metric-label">平台手续费预留</span><strong>${display(summary.feeUsd)}</strong></div>
      <div class="metric-card"><span class="metric-label">退款损失预留</span><strong>${display(summary.refundUsd)}</strong></div>
      <div class="metric-card ${resultClass}"><span class="metric-label">保本广告成本 CPR</span><strong>${display(summary.breakEvenCprUsd)}</strong></div>
      <div class="metric-card ${resultClass}"><span class="metric-label">整体保本 ROI</span><strong>${roi}</strong></div>
    </div>
    <p class="mt-3 text-xs text-slate-400">${unavailable ? '参数错误时不会计算汇总结果。' : '整体 ROI = 总收入 ÷ 汇总保本广告成本；只纳入完整且数值有效的产品。'}</p>`;
}

function productFieldErrors(result) {
  const labels = { costCny: '产品成本', weightG: '重量', priceUsd: '售价', storeShippingUsd: '店铺运费' };
  const errors = {};
  if (result.status === 'incomplete') {
    for (const [key, label] of Object.entries(labels)) {
      if (result.missingFields.includes(label)) errors[key] = `请填写${label}`;
    }
  }
  if (result.status === 'invalid') {
    for (const key of result.invalidFields || []) errors[key] = `${labels[key] || key}不能为负数或无效数值`;
  }
  return errors;
}

function resultMarkup(result, index, fieldErrors) {
  if (result.status === 'incomplete') {
    return `<div class="result-box incomplete"><strong>待补全</strong>${Object.entries(fieldErrors).map(([key, message]) => `<small id="product-${index}-${key}-error">${escapeHtml(message)}</small>`).join('')}</div>`;
  }
  if (result.status === 'invalid') {
    const messages = [...(result.errors || [])];
    return `<div class="result-box invalid"><strong>输入有误</strong>${Object.entries(fieldErrors).map(([key, message]) => `<small id="product-${index}-${key}-error">${escapeHtml(message)}</small>`).join('')}<small>${escapeHtml(messages.join('；'))}</small></div>`;
  }
  const title = result.status === 'loss' ? '亏损 / 无可用广告成本' : '可投放';
  const css = result.status === 'loss' ? 'loss' : 'valid';
  const roi = result.roi === null ? '无效（亏损）' : formatRoi(result.roi);
  return `<div class="result-box ${css}"><strong>${title}</strong><small>收入 $${CalculatorCore.formatMoney(result.totalRevenueUsd)} · 成本 $${CalculatorCore.formatMoney(result.productCostUsd)}</small><small>物流 $${CalculatorCore.formatMoney(result.logisticsUsd)} · 手续费 $${CalculatorCore.formatMoney(result.feeUsd)} · 退款 $${CalculatorCore.formatMoney(result.refundUsd)}</small><small class="font-semibold">保本 CPR $${CalculatorCore.formatMoney(result.breakEvenCprUsd)} · ROI ${roi}</small></div>`;
}

function productInput(product, field, label, type = 'number', errorMessage, errorId) {
  const css = field === 'sku' ? 'input-sku' : '';
  const step = type === 'number' ? 'step="any" inputmode="decimal"' : '';
  const accessibleError = errorMessage ? `aria-invalid="true" aria-describedby="${errorId}"` : '';
  return `<input class="${css}" data-product-id="${escapeHtml(product.id)}" data-field="${field}" type="${type}" ${step} value="${escapeHtml(product[field])}" aria-label="${label}" ${accessibleError}>`;
}

function renderProducts(results) {
  const resultById = new Map(results.map(result => [result.id, result]));
  const rows = state.products.map((product, index) => {
    const result = resultById.get(product.id);
    const fieldErrors = productFieldErrors(result);
    const input = (field, label, type) => productInput(product, field, label, type, fieldErrors[field], `product-${index}-${field}-error`);
    return `<tr>
    <td data-label="序号" class="font-medium text-slate-500 text-center">${index + 1}</td>
    <td data-label="SKU">${input('sku', `第 ${index + 1} 个产品 SKU`, 'text')}</td>
    <td data-label="产品成本 (¥)">${input('costCny', `第 ${index + 1} 个产品成本`)}</td>
    <td data-label="重量 (g)">${input('weightG', `第 ${index + 1} 个产品重量`)}</td>
    <td data-label="售价 ($)">${input('priceUsd', `第 ${index + 1} 个产品售价`)}</td>
    <td data-label="店铺运费 ($)">${input('storeShippingUsd', `第 ${index + 1} 个产品店铺运费`)}</td>
    <td data-label="计算结果"><div id="product-${index}-result">${resultMarkup(result, index, fieldErrors)}</div></td>
    <td data-label="操作"><div class="row-action-btns"><button type="button" class="btn-copy" data-action="copy" data-product-id="${escapeHtml(product.id)}">复制</button><button type="button" class="btn-delete" data-action="delete" data-product-id="${escapeHtml(product.id)}">删除</button></div></td>
  </tr>`;
  }).join('');

  const proj = currentProjectId ? PROJECTS_DATA[currentProjectId] : null;
  const subtitle = proj 
    ? `当前项目「${escapeHtml(proj.name)}」· 已填入规格信息，支持独立保存` 
    : '自由独立计算模式 · 数据保存在浏览器草稿，不影响任何系统项目';

  document.getElementById('products').innerHTML = `
    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
      <div>
        <h2 id="products-title" class="text-base font-bold text-slate-900">产品明细列表</h2>
        <p class="text-xs text-slate-500 mt-0.5">${subtitle}</p>
      </div>
      <div class="flex items-center gap-2">
        ${currentProjectId ? `<button type="button" data-action="save-project" class="btn-primary-action" style="padding: 0.35rem 0.85rem; font-size: 0.8125rem;">💾 保存此产品利润表</button>` : ''}
        <button type="button" data-action="add" class="btn-secondary-action" style="padding: 0.35rem 0.85rem; font-size: 0.8125rem;">+ 新增产品规格</button>
      </div>
    </div>
    <div class="overflow-x-auto"><table class="calc-table"><thead><tr><th class="w-12 text-center">序号</th><th>SKU / 规格名称</th><th>产品成本 (¥)</th><th>重量 (g)</th><th>售价 ($)</th><th>店铺运费 ($)</th><th>计算结果</th><th>操作</th></tr></thead><tbody>${rows}</tbody></table></div>`;
}

function renderDataActions() {
  const proj = currentProjectId ? PROJECTS_DATA[currentProjectId] : null;
  const tips = proj 
    ? `当前正在为「${escapeHtml(proj.name)}」测算，点击「保存当前产品利润表」可持久化保存在系统中；也可导出 JSON 备份。`
    : '自由独立测算数据保存在当前浏览器本地；导出 JSON 文件可在其他电脑或浏览器上随时还原。';

  document.getElementById('data-actions').innerHTML = `
    <h2 id="data-title" class="text-base font-bold text-slate-900 mb-1">数据备份与管理</h2>
    <p class="text-xs text-slate-500 mb-3">${tips}</p>
    <div class="flex flex-wrap gap-2">
      <button type="button" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-action="export">导出备份 (JSON)</button>
      <button type="button" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-action="import">导入备份 (JSON)</button>
      <button type="button" class="rounded-lg border border-rose-300 bg-white px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" data-action="reset">重置当前数据</button>
    </div>`;
}

function render() {
  const settingsErrors = CalculatorCore.validateSettings(state.settings);
  const results = state.products.map(product => CalculatorCore.calculateProduct(product, state.settings));
  const summary = CalculatorCore.calculateSummary(state.products, state.settings);
  renderSettings(settingsErrors);
  renderSummary(summary, settingsErrors);
  renderProducts(results, settingsErrors);
  renderDataActions();
  showStatus(statusMessage);
}

function renderCalculations() {
  const settingsErrors = CalculatorCore.validateSettings(state.settings);
  const results = state.products.map(product => CalculatorCore.calculateProduct(product, state.settings));
  renderSettingsErrors(settingsErrors);
  renderSummary(CalculatorCore.calculateSummary(state.products, state.settings), settingsErrors);
  results.forEach((result, index) => {
    const fieldErrors = productFieldErrors(result);
    const node = document.getElementById(`product-${index}-result`);
    if (node) node.innerHTML = resultMarkup(result, index, fieldErrors);
    for (const input of document.querySelectorAll('input[data-product-id]')) {
      if (input.dataset.productId === state.products[index].id) {
        const field = input.dataset.field;
        updateInputError(input, fieldErrors[field], `product-${index}-${field}-error`);
      }
    }
  });
  showStatus(statusMessage);
}

function updateModeUI(savedAt = null) {
  const badge = document.getElementById('mode-badge');
  const saveBtn = document.getElementById('btn-save-project-profit');
  const modeHint = document.getElementById('erp-mode-hint');
  const lastSaved = document.getElementById('erp-last-saved');

  if (!currentProjectId) {
    if (badge) {
      badge.className = 'mode-status-badge free';
      badge.innerHTML = '<span class="dot"></span>自由独立计算模式';
    }
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.title = '自由模式无需手动保存，浏览器本地自动缓存';
    }
    if (modeHint) {
      modeHint.innerHTML = '💡 <strong>自由独立计算模式</strong>：不关联系统项目，数据保存在当前浏览器本地，可自由测算。如需专属保存请在上方选择产品。';
    }
    if (lastSaved) lastSaved.textContent = '';
  } else {
    const proj = PROJECTS_DATA[currentProjectId];
    const hasSaved = Boolean(proj && proj.profit_data && proj.profit_data.products && proj.profit_data.products.length > 0);
    const timeStr = savedAt ? formatDateTime(savedAt) : (hasSaved ? formatDateTime(proj.profit_data.saved_at) : '');

    if (badge) {
      if (hasSaved) {
        badge.className = 'mode-status-badge saved';
        badge.innerHTML = '<span class="dot"></span>已保存专属利润表';
      } else {
        badge.className = 'mode-status-badge unsaved';
        badge.innerHTML = '<span class="dot"></span>已带入已知规格 · 待保存';
      }
    }
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.title = '保存当前产品利润表至系统服务端';
    }
    if (modeHint) {
      modeHint.innerHTML = `📦 <strong>当前产品</strong>：${escapeHtml(proj ? proj.name : '')} · 同一价格规格仅自动保留一个（共 ${proj ? proj.skus.length : 0} 个已知规格）。可输入售价并调整参数后点击保存。`;
    }
    if (lastSaved) {
      lastSaved.textContent = timeStr ? `上次保存时间：${timeStr}` : '尚未保存到系统';
    }
  }
}

function switchProject(projectId, isUserInitiated = false) {
  if (!projectId) {
    currentProjectId = null;
    state = StateManager.loadState(storage, StateManager.STORAGE_KEY);
    updateModeUI();
    render();
    if (isUserInitiated) showStatus('已切回自由独立计算模式，已恢复本地草稿。');
    return;
  }

  currentProjectId = Number(projectId);
  const proj = PROJECTS_DATA[currentProjectId];
  if (!proj) return;

  if (proj.profit_data && proj.profit_data.products && proj.profit_data.products.length > 0) {
    // 已有保存的专属利润表：完整还原
    try {
      state = StateManager.normalizeState({
        version: 1,
        settings: proj.profit_data.settings || { ...CalculatorCore.DEFAULT_SETTINGS },
        products: proj.profit_data.products || [],
      });
    } catch {
      state = StateManager.createStateFromSkus(proj.skus);
    }
    updateModeUI(proj.profit_data.saved_at);
    render();
    if (isUserInitiated) {
      showStatus(`已载入「${proj.name}」已保存的专属利润表。`);
    }
  } else {
    // 尚未保存过：自动填入已知规格信息（同一价格仅填 1 个），不足 5 行补足至 5 行
    state = StateManager.createStateFromSkus(proj.skus);
    updateModeUI(null);
    render();
    if (isUserInitiated) {
      const filledCount = state.products.filter(p => p.sku || p.costCny !== '').length;
      showStatus(`已自动填入「${proj.name}」的已知规格（同一价格规格已去重仅保留 1 个，共带入 ${filledCount} 个价格档位），并补齐至 5 行；设置好售价后可点击保存。`);
    }
  }
}

async function saveCurrentProjectProfit() {
  if (!currentProjectId) {
    alert('当前处于自由独立计算模式，数据已自动保存在当前浏览器本地。\n若需保存专属利润表，请先在上方关联一个产品项目。');
    return;
  }

  const proj = PROJECTS_DATA[currentProjectId];
  if (!proj) return;

  const saveBtn = document.getElementById('btn-save-project-profit');
  const saveText = document.getElementById('btn-save-text');
  const tableSaveBtns = document.querySelectorAll('button[data-action="save-project"]');

  if (saveBtn) saveBtn.disabled = true;
  tableSaveBtns.forEach(b => b.disabled = true);
  if (saveText) saveText.textContent = '保存中...';

  try {
    const csrfToken = '{{ csrf_token() }}';
    const response = await fetch(proj.save_url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        settings: state.settings,
        products: state.products,
      }),
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || '保存失败，请稍后重试');
    }

    // 更新内存中的 profit_data
    proj.profit_data = result.profit_data;
    updateModeUI(result.profit_data.saved_at);
    showStatus(`✅ ${result.message}`);
  } catch (error) {
    alert('保存失败：' + error.message);
    showStatus(`保存失败：${error.message}`);
  } finally {
    if (saveBtn) saveBtn.disabled = false;
    tableSaveBtns.forEach(b => b.disabled = false);
    if (saveText) saveText.textContent = '保存当前产品利润表';
  }
}

function downloadBackup() {
  try {
    const blob = new Blob([StateManager.exportBackup(state)], { type: 'application/json' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    const prefix = currentProjectId ? `NC-ERP-项目-${PROJECTS_DATA[currentProjectId]?.code || 'PROJ'}-利润表` : 'NC-ERP-自由测算备份';
    link.download = `${prefix}-${new Date().toISOString().slice(0, 10)}.json`;
    link.click();
    setTimeout(() => URL.revokeObjectURL(link.href), 0);
    showStatus('备份已导出。');
  } catch (error) {
    showStatus(`导出失败：${error.message}`);
  }
}

async function restoreBackup(file) {
  if (!file) return;
  try {
    const restored = StateManager.importBackup(await file.text());
    replaceState(restored, '备份已恢复并缓存。');
  } catch (error) {
    showStatus(`导入失败：${error.message}`);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  try { storage = localStorage; } catch { storage = null; }

  const projectSelect = document.getElementById('erp-project-select');
  const btnSaveProject = document.getElementById('btn-save-project-profit');

  // 初始模式切换（根据预选项目或默认为自由模式）
  if (INITIAL_PROJECT_ID && PROJECTS_DATA[INITIAL_PROJECT_ID]) {
    projectSelect.value = String(INITIAL_PROJECT_ID);
    switchProject(INITIAL_PROJECT_ID, false);
  } else {
    projectSelect.value = '';
    switchProject(null, false);
  }

  // 下拉切换即时联动
  projectSelect?.addEventListener('change', () => {
    const val = projectSelect.value;
    switchProject(val ? Number(val) : null, true);
  });

  // 保存按钮
  btnSaveProject?.addEventListener('click', () => {
    saveCurrentProjectProfit();
  });

  // 快捷键支持 (Ctrl+S / Cmd+S)
  window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
      if (currentProjectId) {
        e.preventDefault();
        saveCurrentProjectProfit();
      }
    }
  });

  // 输入监听
  document.addEventListener('input', event => {
    const setting = event.target.dataset.setting;
    if (setting) {
      updateAndRender(current => {
        current.settings[setting] = ['feeRate', 'refundRate'].includes(setting)
          ? (event.target.value === '' ? '' : Number(event.target.value) / 100)
          : event.target.value;
      });
      return;
    }
    const id = event.target.dataset.productId;
    const field = event.target.dataset.field;
    if (id && field) {
      updateAndRender(current => {
        const product = current.products.find(item => item.id === id);
        if (product) product[field] = event.target.value;
      });
    }
  });

  // 按钮动作监听
  document.addEventListener('click', event => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;
    const { action, productId } = button.dataset;
    if (action === 'save-project') saveCurrentProjectProfit();
    if (action === 'add') updateAndRender(current => current.products.push(CalculatorCore.blankProduct(current.products.length)), true);
    if (action === 'copy') updateAndRender(current => {
      const source = current.products.find(product => product.id === productId);
      if (source) current.products.push({ ...source, id: CalculatorCore.blankProduct(current.products.length).id });
    }, true);
    if (action === 'delete') updateAndRender(current => {
      if (current.products.length === 1) current.products[0] = CalculatorCore.blankProduct(0);
      else current.products = current.products.filter(product => product.id !== productId);
    }, true);
    if (action === 'export') downloadBackup();
    if (action === 'import') document.getElementById('backup-file').click();
    if (action === 'reset') {
      if (currentProjectId) {
        if (window.confirm('确定要重置当前项目的利润表吗？这会将规格信息恢复为系统已知规格并清空填写的售价。')) {
          const proj = PROJECTS_DATA[currentProjectId];
          state = StateManager.createStateFromSkus(proj ? proj.skus : []);
          StateManager.saveState(storage, state, getActiveStorageKey());
          render();
          showStatus('已重置为初始已知规格信息。');
        }
      } else {
        if (window.confirm('确定要重置全部参数和产品吗？此操作会清除当前浏览器中的自由计算草稿。')) {
          replaceState(StateManager.createInitialState(), '已重置为默认参数和 5 行空白产品。');
        }
      }
    }
  });

  document.getElementById('backup-file').addEventListener('change', async event => {
    await restoreBackup(event.target.files[0]);
    event.target.value = '';
  });
});
    </script>
</x-layouts.app>
