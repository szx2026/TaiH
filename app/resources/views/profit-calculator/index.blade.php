<x-layouts.app :title="'产品盈亏计算工具 · NC ERP'">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-teal-600">离线计算 · 数据保存在当前浏览器</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">产品盈亏计算工具</h1>
            <p class="mt-2 text-sm text-slate-500">填写成本、重量和售价，实时计算保本广告成本（CPR）与盈亏保本 ROI。</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700 border border-teal-200">
                <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                实时自动保存
            </span>
        </div>
    </div>

    <!-- ERP 项目规格快速导入条 -->
    <section class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                <span class="text-sm font-semibold text-slate-800">从系统项目快速导入规格：</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 flex-1 max-w-xl">
                <select id="erp-project-select" class="field-input mt-0 text-sm flex-1">
                    <option value="">-- 选择系统中的产品项目 --</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" @selected($preloadedProject?->id === $proj->id) data-skus='@json($proj->skus)'>
                            {{ $proj->product_name }} ({{ $proj->skus->count() }} 个规格) · {{ $proj->project_code }}
                        </option>
                    @endforeach
                </select>
                <button type="button" id="btn-import-project" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 transition">
                    导入规格
                </button>
            </div>
        </div>
        <p id="erp-import-hint" class="mt-2 text-xs text-slate-400">导入会将所选项目的规格名称、内部 SKU、采购价（CNY）及重量（g）填入计算表中，自动带入利润测算。</p>
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
  const STORAGE_KEY = 'nc-erp-product-profit-calculator-v1';
  function createInitialState() {
    return { version: 1, settings: { ...Core.DEFAULT_SETTINGS },
      products: Array.from({ length: 5 }, (_, i) => Core.blankProduct(i)) };
  }
  function normalizeState(value) {
    if (!value || value.version !== 1) throw new Error('不支持的备份版本');
    if (!value.settings || typeof value.settings !== 'object' || Array.isArray(value.settings))
      throw new Error('参数格式无效');
    if (!Array.isArray(value.products) || value.products.length < 1)
      throw new Error('备份内容不完整');
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
      if (productIds.has(normalized.id)) throw new Error(`第 ${index + 1} 个产品 ID 重复`);
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
  function loadState(storage) {
    try { const raw = storage.getItem(STORAGE_KEY); return raw ? importBackup(raw) : createInitialState(); }
    catch { return createInitialState(); }
  }
  function saveState(storage, state) {
    storage.setItem(STORAGE_KEY, exportBackup(state));
  }
  return { STORAGE_KEY, createInitialState, normalizeState,
    loadState, saveState, exportBackup, importBackup };
});

let state;
let statusMessage = '';
let storage = null;

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>'"]/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;',
  }[char]));
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
    StateManager.normalizeState(candidate);
    if (!storage) throw new Error('浏览器存储不可用');
    StateManager.saveState(storage, candidate);
    state = candidate;
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
    if (!storage) throw new Error('浏览器存储不可用');
    StateManager.saveState(storage, state);
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
  document.getElementById('products').innerHTML = `
    <div class="flex items-center justify-between gap-4 mb-3"><h2 id="products-title" class="text-base font-bold text-slate-900">产品明细列表</h2><button type="button" data-action="add" class="rounded-lg bg-teal-700 px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-teal-800 transition">+ 新增产品</button></div>
    <div class="overflow-x-auto"><table class="calc-table"><thead><tr><th class="w-12 text-center">序号</th><th>SKU / 规格名称</th><th>产品成本 (¥)</th><th>重量 (g)</th><th>售价 ($)</th><th>店铺运费 ($)</th><th>计算结果</th><th>操作</th></tr></thead><tbody>${rows}</tbody></table></div>`;
}

function renderDataActions() {
  document.getElementById('data-actions').innerHTML = `
    <h2 id="data-title" class="text-base font-bold text-slate-900 mb-1">数据备份与管理</h2>
    <p class="text-xs text-slate-500 mb-3">测算数据自动保存在当前浏览器本地；导出 JSON 文件可在其他电脑或浏览器上随时还原。</p>
    <div class="flex flex-wrap gap-2">
      <button type="button" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-action="export">导出备份 (JSON)</button>
      <button type="button" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-action="import">导入备份 (JSON)</button>
      <button type="button" class="rounded-lg border border-rose-300 bg-white px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" data-action="reset">重置全部数据</button>
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

function downloadBackup() {
  try {
    const blob = new Blob([StateManager.exportBackup(state)], { type: 'application/json' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `NC-ERP-产品盈亏测算备份-${new Date().toISOString().slice(0, 10)}.json`;
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
    replaceState(restored, '备份已恢复并保存。');
  } catch (error) {
    showStatus(`导入失败：${error.message}`);
  }
}

function importProjectSkus(skus, projectName) {
  if (!skus || !skus.length) {
    alert(`该项目「${projectName}」尚未录入产品规格或 SKU。`);
    return;
  }
  const newProducts = skus.map((sku, idx) => ({
    id: `erp-sku-${Date.now()}-${idx}`,
    sku: sku.sku_code ? `${sku.variant_name} (${sku.sku_code})` : sku.variant_name,
    costCny: sku.purchase_price !== null && sku.purchase_price !== undefined ? Number(sku.purchase_price) : '',
    weightG: sku.weight_g !== null && sku.weight_g !== undefined ? Number(sku.weight_g) : '',
    priceUsd: '',
    storeShippingUsd: 0,
  }));

  updateAndRender(current => {
    current.products = newProducts;
  }, true);

  showStatus(`已成功从「${projectName}」导入 ${newProducts.length} 个规格；请填写预估售价（$）以计算保本 CPR 与 ROI。`);
}

document.addEventListener('DOMContentLoaded', () => {
  try { storage = localStorage; } catch { storage = null; }
  state = StateManager.loadState(storage);
  if (!storage) statusMessage = '浏览器存储不可用。你仍可计算和导出备份，但当前更改不会自动保存。';
  render();

  // 检查是否有预选项目
  const projectSelect = document.getElementById('erp-project-select');
  const btnImportProject = document.getElementById('btn-import-project');

  btnImportProject?.addEventListener('click', () => {
    const selectedOption = projectSelect.selectedOptions[0];
    if (!selectedOption || !selectedOption.value) {
      alert('请先选择一个产品项目！');
      return;
    }
    try {
      const skus = JSON.parse(selectedOption.dataset.skus || '[]');
      const projectName = selectedOption.textContent.trim().split('(')[0].trim();
      importProjectSkus(skus, projectName);
    } catch (e) {
      alert('解析规格数据失败：' + e.message);
    }
  });

  // 如果 URL 传参携带 project 且当前数据还是空模板时，自动触发导入一次
  @if($preloadedProject && $preloadedProject->skus->isNotEmpty())
    const preloadedSkus = @json($preloadedProject->skus);
    const preloadedName = @json($preloadedProject->product_name);
    // 只有当当前列表全为空时自动填充，避免覆盖已有草稿
    const isAllBlank = state.products.every(p => !p.sku && !p.costCny && !p.priceUsd);
    if (isAllBlank) {
      importProjectSkus(preloadedSkus, preloadedName);
    }
  @endif

  document.addEventListener('input', event => {
    const setting = event.target.dataset.setting;
    if (setting) {
      updateAndRender(current => { current.settings[setting] = ['feeRate', 'refundRate'].includes(setting) ? (event.target.value === '' ? '' : Number(event.target.value) / 100) : event.target.value; });
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

  document.addEventListener('click', event => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;
    const { action, productId } = button.dataset;
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
    if (action === 'reset' && window.confirm('确定要重置全部参数和产品吗？此操作会清除当前浏览器中的计算数据。')) {
      replaceState(StateManager.createInitialState(), '已重置为默认参数和 5 行空白产品。');
    }
  });

  document.getElementById('backup-file').addEventListener('change', async event => {
    await restoreBackup(event.target.files[0]);
    event.target.value = '';
  });
});
    </script>
</x-layouts.app>
