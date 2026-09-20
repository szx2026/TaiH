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
        /* 强制所有 SVG 尺寸受控，杜绝全屏放大 */
        svg {
            flex-shrink: 0;
            display: inline-block;
            vertical-align: middle;
        }

        /* 顶部 Tab 导航 */
        .orders-nav-tabs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }
        .orders-nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            color: #64748b;
            transition: all 0.15s ease;
        }
        .orders-nav-tab:hover {
            color: #334155;
            border-bottom-color: #cbd5e1;
        }
        .orders-nav-tab.active {
            color: #0f766e !important;
            border-bottom-color: #0f766e !important;
            font-weight: 700;
        }

        /* 顶部标题行 */
        .orders-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .orders-header-badge {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #0f766e;
            margin-bottom: 0.25rem;
        }
        .orders-header-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin: 0.25rem 0;
        }
        .orders-header-sub {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
        }
        .orders-action-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* 按钮样式 (显式颜色与防换行) */
        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #ffffff !important;
            background-color: #0f766e !important;
            border: 1px solid #0f766e !important;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease;
        }
        .btn-primary-action:hover:not(:disabled) {
            background-color: #115e59 !important;
        }
        .btn-primary-action:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-secondary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155 !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease;
        }
        .btn-secondary-action:hover:not(:disabled) {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
        }
        .btn-secondary-action:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* 上半部分双栏卡片网格 */
        .top-controls-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 960px) {
            .top-controls-row {
                grid-template-columns: 1fr;
            }
        }
        .control-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.875rem;
        }
        .card-step-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: #1e293b;
            font-size: 0.9375rem;
        }
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: #ccfbf1;
            color: #0f766e;
            font-size: 0.75rem;
            font-weight: 800;
        }

        /* CSV 拖拽区域 */
        .csv-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            background-color: #f8fafc;
            padding: 1.25rem 1rem;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.15s ease;
        }
        .csv-dropzone:hover {
            border-color: #0d9488;
            background-color: #f0fdfa;
        }
        .csv-dropzone.dropzone-active {
            border-color: #0d9488 !important;
            background-color: #ecfdf5 !important;
        }
        .csv-dropzone-icon {
            width: 36px !important;
            height: 36px !important;
            max-width: 36px !important;
            max-height: 36px !important;
            color: #0d9488;
            margin: 0 auto 0.4rem auto;
            display: block;
        }
        .csv-dropzone-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .csv-dropzone-sub {
            font-size: 0.75rem;
            color: #94a3b8;
            margin: 0.25rem 0 0 0;
        }
        .dropzone-file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* 底部统计栏 */
        .csv-meta-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.65rem 0.5rem;
            margin-top: 1rem;
            text-align: center;
        }
        .csv-meta-item .meta-label {
            font-size: 0.6875rem;
            color: #64748b;
            display: block;
            margin-bottom: 0.15rem;
        }
        .csv-meta-item .meta-num {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            display: block;
        }

        /* 广告花费高亮栏 */
        .ad-spend-banner {
            border: 2px solid #5eead4;
            background: #f0fdfa;
            border-radius: 10px;
            padding: 0.875rem 1rem;
            margin-bottom: 1rem;
        }
        .ad-spend-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #134e4a;
            letter-spacing: 0.025em;
            margin-bottom: 0.35rem;
        }
        .ad-spend-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .ad-spend-prefix {
            position: absolute;
            left: 0.75rem;
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f766e;
            pointer-events: none;
            z-index: 2;
        }
        .ad-spend-input {
            width: 100%;
            border: 1px solid #99f6e4;
            border-radius: 8px;
            padding: 0.5rem 0.75rem 0.5rem 2.1rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: #134e4a;
            background: #ffffff;
            outline: none;
            transition: all 0.15s;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.03);
            box-sizing: border-box;
        }
        .ad-spend-input:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.2);
        }

        /* 参数网格 */
        .params-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.65rem;
        }
        @media (max-width: 580px) {
            .params-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .param-box label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.25rem;
            white-space: nowrap;
        }
        .param-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.4rem 0.55rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1e293b;
            background: #ffffff;
            box-sizing: border-box;
        }
        .param-input:focus {
            border-color: #0d9488;
            outline: none;
        }

        /* 核心利润指标 KPI 看板 */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1080px) {
            .kpi-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 540px) {
            .kpi-row {
                grid-template-columns: 1fr;
            }
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.15rem;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.03);
            box-sizing: border-box;
        }
        .kpi-card.highlight-profit {
            background: #f0fdf4 !important;
            border-color: #86efac !important;
        }
        .kpi-card.highlight-loss {
            background: #fff1f2 !important;
            border-color: #fecdd3 !important;
        }
        .kpi-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
        }
        .kpi-value-row {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .kpi-val-main {
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #0f172a;
            line-height: 1.1;
        }
        .kpi-val-sub {
            font-size: 0.75rem;
            color: #64748b;
        }
        .kpi-foot-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.75rem;
            padding-top: 0.5rem;
            border-top: 1px solid #f1f5f9;
        }
        .kpi-foot-bar strong {
            color: #1e293b;
            font-size: 0.8125rem;
        }

        /* 成本结构拆解网格 */
        .cost-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03);
            margin-bottom: 1.5rem;
        }
        .cost-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.85rem;
            margin-top: 0.875rem;
        }
        @media (max-width: 960px) {
            .cost-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 520px) {
            .cost-grid {
                grid-template-columns: 1fr;
            }
        }
        .cost-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.85rem;
            box-sizing: border-box;
        }
        .cost-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #64748b;
        }
        .cost-card-amount {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            margin-top: 0.35rem;
        }
        .cost-card-note {
            font-size: 0.6875rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        /* 数据明细表格 */
        .table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03);
            margin-bottom: 2.5rem;
            overflow: hidden;
        }
        .table-header-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            gap: 0.75rem;
        }
        .table-nav-btns {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .table-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            color: #64748b;
            background: transparent;
            border-top: none;
            border-left: none;
            border-right: none;
            cursor: pointer;
            transition: all 0.15s;
        }
        .table-tab-btn.active {
            color: #0f766e !important;
            border-bottom-color: #0f766e !important;
            font-weight: 700;
        }
        .table-search-input {
            width: 240px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            color: #1e293b;
            box-sizing: border-box;
        }
        .table-search-input:focus {
            border-color: #0d9488;
            outline: none;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            text-align: left;
        }
        .orders-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .orders-table td {
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        .orders-table tr:hover td {
            background-color: #f8fafc;
        }
        .badge-status {
            display: inline-block;
            border-radius: 4px;
            padding: 0.15rem 0.4rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-status.paid {
            background: #dcfce7;
            color: #166534;
        }
        .badge-status.other {
            background: #f1f5f9;
            color: #475569;
        }
    </style>

    <!-- 功能切换 Tab -->
    <div class="orders-nav-tabs">
        <a href="{{ route('profit-calculator.index') }}" class="orders-nav-tab">
            <svg width="16" height="16" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            单品保本盈亏测算
        </a>
        <a href="{{ route('order-profit-calculator.index') }}" class="orders-nav-tab active">
            <svg width="16" height="16" style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            每日订单利润核算 (Shopify + FB)
        </a>
    </div>

    <!-- 顶部标题与快捷操作栏 -->
    <div class="orders-header">
        <div>
            <div class="orders-header-badge">出海电商核算工具 · Shopify 订单与 Facebook 广告一站式毛利清算</div>
            <h1 class="orders-header-title">每日订单利润核算</h1>
            <p class="orders-header-sub">上传 Shopify 当日出单 CSV，自动匹配 ERP 产品采购价与跨境物流，填入 FB 广告花费即时获取净利润、真实 ROAS 与客单价。</p>
        </div>
        <div class="orders-action-group">
            <button type="button" id="btn-load-sample" class="btn-secondary-action">
                <svg width="15" height="15" style="width:15px;height:15px;color:#0f766e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>载入测试示例数据</span>
            </button>
            <button type="button" id="btn-export-csv" class="btn-secondary-action" disabled>
                <svg width="15" height="15" style="width:15px;height:15px;color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>导出核算报表 (CSV)</span>
            </button>
            <button type="button" id="btn-clear-data" class="btn-secondary-action">
                <svg width="15" height="15" style="width:15px;height:15px;color:#94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>清空数据</span>
            </button>
        </div>
    </div>

    <!-- 上半部分：左侧 CSV 上传与订单识别，右侧 广告花费与计算参数 -->
    <div class="top-controls-row">
        <!-- 左侧：Shopify CSV 上传卡片 -->
        <div class="control-card">
            <div>
                <div class="card-header-bar">
                    <div class="card-step-title">
                        <span class="step-number">1</span>
                        <span>导入 Shopify 当日订单 (CSV)</span>
                    </div>
                    <span id="upload-badge" style="display:none;font-size:0.75rem;font-weight:600;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:0.2rem 0.6rem;border-radius:9999px;">
                        ✓ 已成功识别
                    </span>
                </div>

                <!-- 拖拽上传框 -->
                <div id="drop-zone" class="csv-dropzone">
                    <input type="file" id="file-input" accept=".csv,text/csv" class="dropzone-file-input" />
                    <svg width="36" height="36" class="csv-dropzone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p class="csv-dropzone-text">点击或将 Shopify Orders CSV 拖拽到此处</p>
                    <p class="csv-dropzone-sub">支持 Shopify 标准订单导出格式（跨行多商品自动汇总结算）</p>
                </div>

                <!-- 订单筛选与识别概括 -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;font-size:0.75rem;color:#64748b;">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-weight:600;color:#334155;">订单状态筛选:</span>
                        <select id="filter-status" style="border:1px solid #cbd5e1;border-radius:4px;padding:0.2rem 0.4rem;font-size:0.75rem;background:#ffffff;">
                            <option value="paid" selected>仅计算已付款 (paid) 订单</option>
                            <option value="all">计算全部状态订单</option>
                        </select>
                    </div>
                    <div id="file-info-text" style="color:#94a3b8;">暂未上传文件</div>
                </div>
            </div>

            <!-- 数据统计小脚标 -->
            <div id="summary-meta-bar" class="csv-meta-summary">
                <div class="csv-meta-item">
                    <span class="meta-label">总订单行数</span>
                    <span id="meta-total-lines" class="meta-num">0</span>
                </div>
                <div class="csv-meta-item">
                    <span class="meta-label">有效订单数</span>
                    <span id="meta-orders-count" class="meta-num" style="color:#0f766e;">0</span>
                </div>
                <div class="csv-meta-item">
                    <span class="meta-label">售出商品件数</span>
                    <span id="meta-items-count" class="meta-num">0</span>
                </div>
                <div class="csv-meta-item">
                    <span class="meta-label">出单 SKU 种类</span>
                    <span id="meta-skus-count" class="meta-num">0</span>
                </div>
            </div>
        </div>

        <!-- 右侧：广告花费与参数设置卡片 -->
        <div class="control-card">
            <div>
                <div class="card-header-bar">
                    <div class="card-step-title">
                        <span class="step-number">2</span>
                        <span>今日 FB 广告花费与核算参数</span>
                    </div>
                    <span style="font-size:0.75rem;color:#94a3b8;">修改任意项即时重算</span>
                </div>

                <!-- Facebook 广告花费突出输入框 -->
                <div class="ad-spend-banner">
                    <label class="ad-spend-label">今日 Facebook 广告总花费 (USD $)</label>
                    <div class="ad-spend-input-wrap">
                        <span class="ad-spend-prefix">$</span>
                        <input type="number" id="input-fb-ad-spend" step="0.01" min="0" placeholder="0.00" value="0.00" class="ad-spend-input" />
                    </div>
                    <div style="font-size:0.6875rem;color:#0f766e;margin-top:0.35rem;display:flex;align-items:center;gap:0.25rem;">
                        <svg width="13" height="13" style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>输入 Meta Ads 后台今日的总花费（可带小数），系统将用于计算 ROAS 与净利润。</span>
                    </div>
                </div>

                <!-- 成本与汇率参数网格 -->
                <div class="params-row">
                    <div class="param-box">
                        <label>汇率 (USD➔CNY)</label>
                        <input type="number" id="param-exchange-rate" step="0.01" value="7.20" class="param-input" />
                    </div>
                    <div class="param-box">
                        <label title="发一个包裹的固定基础费用（处理费/挂号费），若货代纯按重量收费可填 0">物流基础费/单 (¥)</label>
                        <input type="number" id="param-shipping-base" step="0.5" value="30.00" class="param-input" />
                    </div>
                    <div class="param-box">
                        <label>续重费率 (¥/g)</label>
                        <input type="number" id="param-shipping-rate" step="0.01" value="0.05" class="param-input" />
                    </div>
                    <div class="param-box">
                        <label>网关费率 (%)</label>
                        <input type="number" id="param-payment-fee-rate" step="0.1" value="3.0" class="param-input" />
                    </div>
                </div>
            </div>

            <!-- 参数说明 -->
            <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;font-size:0.6875rem;color:#94a3b8;">
                <span>跨境物流：单笔物流基础费 + (整单总重量 × 续重费率)</span>
                <span>网关扣款：销售额 × 费率 + $0.30/单</span>
            </div>
        </div>
    </div>

    <!-- 未匹配 SKU 补齐提示条 -->
    <div id="missing-skus-alert" style="display:none;background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:1rem;margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <svg width="20" height="20" style="width:20px;height:20px;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h3 style="margin:0;font-size:0.875rem;font-weight:700;color:#92400e;">发现 <span id="missing-skus-count">0</span> 个出单 SKU 在 ERP 中未录入采购成本或重量</h3>
                <p style="margin:0.2rem 0 0 0;font-size:0.75rem;color:#b45309;">请在下方快速补齐单价与重量，系统将即刻将其计入采购与物流成本：</p>
            </div>
        </div>
        <div id="missing-skus-list" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:0.75rem;margin-top:0.75rem;">
            <!-- 动态生成未匹配 SKU 快捷输入卡片 -->
        </div>
    </div>

    <!-- 核心利润看板 (Summary KPI Dashboard) -->
    <div class="kpi-row">
        <!-- 卡片 1: 今日净利润 -->
        <div id="card-net-profit" class="kpi-card highlight-profit">
            <div class="kpi-top-bar">
                <span>今日净利润 (Net Profit)</span>
                <span id="badge-profit-status" style="border-radius:9999px;padding:0.15rem 0.5rem;font-size:0.6875rem;font-weight:700;background:#dcfce7;color:#166534;border:1px solid #86efac;">盈利</span>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-val-main" id="kpi-net-profit-usd" style="color:#15803d;">$0.00</span>
                <span class="kpi-val-sub" id="kpi-net-profit-cny">≈ ¥0.00</span>
            </div>
            <div class="kpi-foot-bar">
                <span>实际净利率:</span>
                <strong id="kpi-net-margin" style="color:#166534;">0.0%</strong>
            </div>
        </div>

        <!-- 卡片 2: 真实 ROAS -->
        <div class="kpi-card">
            <div class="kpi-top-bar">
                <span>真实投产比 (ROAS)</span>
                <span style="font-size:0.6875rem;color:#64748b;background:#f1f5f9;padding:0.15rem 0.4rem;border-radius:4px;">总营收 ÷ FB花费</span>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-val-main" id="kpi-roas">0.00</span>
                <span style="font-size:0.875rem;font-weight:700;color:#94a3b8;">x</span>
            </div>
            <div class="kpi-foot-bar">
                <span>保本 ROAS 目标:</span>
                <strong id="kpi-breakeven-roas">--</strong>
            </div>
        </div>

        <!-- 卡片 3: 今日总销售额 -->
        <div class="kpi-card">
            <div class="kpi-top-bar">
                <span>今日总销售额 (Revenue)</span>
                <span style="font-size:0.6875rem;color:#64748b;background:#f1f5f9;padding:0.15rem 0.4rem;border-radius:4px;">Shopify 订单总入账</span>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-val-main" id="kpi-gross-revenue">$0.00</span>
                <span class="kpi-val-sub" id="kpi-gross-revenue-cny">≈ ¥0.00</span>
            </div>
            <div class="kpi-foot-bar">
                <span>客单价 (AOV):</span>
                <strong id="kpi-aov">$0.00</strong>
            </div>
        </div>

        <!-- 卡片 4: 广告获客与出单效率 -->
        <div class="kpi-card">
            <div class="kpi-top-bar">
                <span>单均广告成本 (CPA)</span>
                <span style="font-size:0.6875rem;color:#64748b;background:#f1f5f9;padding:0.15rem 0.4rem;border-radius:4px;">FB花费 ÷ 订单数</span>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-val-main" id="kpi-cpa">$0.00</span>
                <span class="kpi-val-sub" id="kpi-order-count-tag">0 笔订单</span>
            </div>
            <div class="kpi-foot-bar">
                <span>售出总件数:</span>
                <strong id="kpi-total-items">0 件</strong>
            </div>
        </div>
    </div>

    <!-- 成本深度拆解看板 -->
    <div class="cost-section">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:0.4rem;font-size:0.9375rem;font-weight:700;color:#1e293b;">
                <svg width="18" height="18" style="width:18px;height:18px;color:#0f766e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                <span>今日成本结构拆解与占比分析</span>
            </div>
            <span style="font-size:0.75rem;color:#94a3b8;">总成本 = FB广告 + 产品采购 + 跨境物流 + 平台手续费</span>
        </div>

        <div class="cost-grid">
            <!-- 成本 1: FB 广告花费 -->
            <div class="cost-card">
                <div class="cost-card-header">
                    <span style="display:flex;align-items:center;gap:0.35rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block;"></span>
                        <span>Meta 广告花费</span>
                    </span>
                    <strong id="cost-percent-ad" style="color:#1e293b;">0.0%</strong>
                </div>
                <div class="cost-card-amount" id="cost-val-ad">$0.00</div>
                <div class="cost-card-note">占比营收: <span id="cost-rev-pct-ad">0.0%</span></div>
            </div>

            <!-- 成本 2: 产品采购成本 -->
            <div class="cost-card">
                <div class="cost-card-header">
                    <span style="display:flex;align-items:center;gap:0.35rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                        <span>产品采购总成本</span>
                    </span>
                    <strong id="cost-percent-purchase" style="color:#1e293b;">0.0%</strong>
                </div>
                <div class="cost-card-amount" id="cost-val-purchase">$0.00</div>
                <div class="cost-card-note" id="cost-val-purchase-cny">折合 ¥0.00</div>
            </div>

            <!-- 成本 3: 跨境物流成本 -->
            <div class="cost-card">
                <div class="cost-card-header">
                    <span style="display:flex;align-items:center;gap:0.35rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#8b5cf6;display:inline-block;"></span>
                        <span>跨境物流总运费</span>
                    </span>
                    <strong id="cost-percent-shipping" style="color:#1e293b;">0.0%</strong>
                </div>
                <div class="cost-card-amount" id="cost-val-shipping">$0.00</div>
                <div class="cost-card-note" id="cost-val-shipping-cny">折合 ¥0.00</div>
            </div>

            <!-- 成本 4: 网关手续费 -->
            <div class="cost-card">
                <div class="cost-card-header">
                    <span style="display:flex;align-items:center;gap:0.35rem;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                        <span>平台与支付网关费</span>
                    </span>
                    <strong id="cost-percent-gateway" style="color:#1e293b;">0.0%</strong>
                </div>
                <div class="cost-card-amount" id="cost-val-gateway">$0.00</div>
                <div class="cost-card-note">费率 3.0% + $0.30/单</div>
            </div>
        </div>
    </div>

    <!-- 表格区域：Tab 标签切换查看【SKU 出单与毛利汇总】vs【订单明细穿透】 -->
    <div class="table-card">
        <div class="table-header-wrap">
            <div class="table-nav-btns">
                <button type="button" id="tab-btn-skus" class="table-tab-btn active">
                    <svg width="15" height="15" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>出单 SKU 销售与毛利汇总 (<span id="tab-skus-count">0</span>)</span>
                </button>
                <button type="button" id="tab-btn-orders" class="table-tab-btn">
                    <svg width="15" height="15" style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>订单详情穿透列表 (<span id="tab-orders-count">0</span>)</span>
                </button>
            </div>

            <!-- 搜索框 -->
            <div>
                <input type="text" id="table-search" placeholder="搜索 SKU / 品名 / 订单号..." class="table-search-input" />
            </div>
        </div>

        <!-- 视图 1: SKU 销售与毛利汇总表 -->
        <div id="view-skus" style="overflow-x:auto;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>SKU 编码</th>
                        <th>关联项目 / 商品名</th>
                        <th style="text-align:center;">出单件数</th>
                        <th style="text-align:right;">总销售额 ($)</th>
                        <th style="text-align:right;">单件采购价 (¥)</th>
                        <th style="text-align:right;">单件重量 (g)</th>
                        <th style="text-align:right;">总采购成本 ($)</th>
                        <th style="text-align:right;">总物流成本 ($)</th>
                        <th style="text-align:right;">预估毛利 ($)</th>
                        <th style="text-align:right;">毛利率</th>
                    </tr>
                </thead>
                <tbody id="tbody-skus">
                    <tr>
                        <td colspan="10" style="padding:2.5rem 1rem;text-align:center;color:#94a3b8;">
                            暂无出单数据，请在上方上传 Shopify 订单 CSV 或点击「载入测试示例数据」
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 视图 2: 订单明细穿透表 -->
        <div id="view-orders" style="display:none;overflow-x:auto;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>订单编号</th>
                        <th>下单时间</th>
                        <th style="text-align:center;">支付状态</th>
                        <th>包含商品条目</th>
                        <th style="text-align:right;">订单入账 ($)</th>
                        <th style="text-align:right;">采购成本 ($)</th>
                        <th style="text-align:right;">物流预估 ($)</th>
                        <th style="text-align:right;">网关扣费 ($)</th>
                        <th style="text-align:right;">单笔订单毛利 ($)</th>
                    </tr>
                </thead>
                <tbody id="tbody-orders">
                    <tr>
                        <td colspan="9" style="padding:2.5rem 1rem;text-align:center;color:#94a3b8;">
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

                for (let i = 1; i < matrix.length; i++) {
                    const row = matrix[i];
                    if (!row || row.length === 0 || (row.length === 1 && !row[0])) continue;

                    const rawName = (row[idxName] || '').trim();
                    const lineSku = idxLineSku !== -1 ? (row[idxLineSku] || '').trim() : '';
                    const lineName = idxLineName !== -1 ? (row[idxLineName] || '').trim() : '';
                    const lineQty = idxLineQty !== -1 ? parseInt(row[idxLineQty], 10) || 0 : 1;
                    const linePrice = idxLinePrice !== -1 ? parseFloat(row[idxLinePrice]) || 0 : 0;

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

                uploadBadge.style.display = 'inline-block';
                fileInfoText.textContent = `已加载: ${sourceName || 'Shopify 订单文件'} (${parsedOrders.length} 笔订单)`;
                btnExportCsv.disabled = false;

                calculateAndRender();
            }

            // 获取 SKU 对应的采购价 (¥) 与重量 (g)
            function getSkuSpecs(skuCode) {
                const cleanCode = (skuCode || '').trim();
                
                if (customOverrides[cleanCode]) {
                    return customOverrides[cleanCode];
                }

                if (erpSkusMap[cleanCode]) {
                    return {
                        purchase_price: erpSkusMap[cleanCode].purchase_price,
                        weight_g: erpSkusMap[cleanCode].weight_g,
                        variant_name: erpSkusMap[cleanCode].variant_name,
                        project_name: erpSkusMap[cleanCode].project_name,
                        is_erp: true
                    };
                }

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

                const validOrders = parsedOrders.filter(ord => {
                    if (!filterPaidOnly) return true;
                    return ord.financial_status === 'paid' || ord.financial_status === 'authorized';
                });

                const missingSkus = new Set();
                let totalItemsCount = 0;
                let grossRevenueUsd = 0;
                let totalPurchaseCny = 0;
                let totalShippingCny = 0;
                let totalPaymentFeeUsd = 0;

                const skuMap = new Map();

                const calculatedOrders = validOrders.map(order => {
                    let orderPurchaseCny = 0;
                    let orderWeightG = 0;
                    let orderItemsCount = 0;

                    order.items.forEach(item => {
                        const specs = getSkuSpecs(item.sku);
                        orderItemsCount += item.quantity;

                        if (specs.purchase_price === null || specs.purchase_price === undefined) {
                            missingSkus.add(item.sku);
                        }

                        const pPrice = specs.purchase_price !== null ? specs.purchase_price : 0;
                        const pWeight = specs.weight_g !== null ? specs.weight_g : 100;

                        const itemPurchaseTotal = pPrice * item.quantity;
                        const itemWeightTotal = pWeight * item.quantity;

                        orderPurchaseCny += itemPurchaseTotal;
                        orderWeightG += itemWeightTotal;

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

                    const orderRevenueUsd = order.total > 0 ? order.total : order.items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
                    grossRevenueUsd += orderRevenueUsd;

                    const orderShippingCny = shippingBaseCny + (orderWeightG * shippingRateCny);
                    totalShippingCny += orderShippingCny;

                    const orderFeeUsd = (orderRevenueUsd * paymentFeePct) + (orderRevenueUsd > 0 ? paymentFeeFixUsd : 0);
                    totalPaymentFeeUsd += orderFeeUsd;

                    totalPurchaseCny += orderPurchaseCny;

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

                const totalPurchaseUsd = totalPurchaseCny / exchangeRate;
                const totalShippingUsd = totalShippingCny / exchangeRate;
                const totalCostsUsd = fbAdSpendUsd + totalPurchaseUsd + totalShippingUsd + totalPaymentFeeUsd;
                const netProfitUsd = grossRevenueUsd - totalCostsUsd;
                const netProfitCny = netProfitUsd * exchangeRate;
                const netMarginPct = grossRevenueUsd > 0 ? (netProfitUsd / grossRevenueUsd) * 100 : 0;

                const realRoas = fbAdSpendUsd > 0 ? (grossRevenueUsd / fbAdSpendUsd) : 0;
                const marginBeforeAd = grossRevenueUsd - totalPurchaseUsd - totalShippingUsd - totalPaymentFeeUsd;
                const breakevenRoas = marginBeforeAd > 0 ? (grossRevenueUsd / marginBeforeAd) : 0;

                const ordersCount = validOrders.length;
                const aovUsd = ordersCount > 0 ? (grossRevenueUsd / ordersCount) : 0;
                const cpaUsd = ordersCount > 0 ? (fbAdSpendUsd / ordersCount) : 0;

                document.getElementById('meta-total-lines').textContent = parsedOrders.length;
                document.getElementById('meta-orders-count').textContent = ordersCount;
                document.getElementById('meta-items-count').textContent = totalItemsCount;
                document.getElementById('meta-skus-count').textContent = skuMap.size;

                const cardNetProfit = document.getElementById('card-net-profit');
                const badgeProfitStatus = document.getElementById('badge-profit-status');
                const kpiNetProfitUsd = document.getElementById('kpi-net-profit-usd');
                const kpiNetProfitCny = document.getElementById('kpi-net-profit-cny');
                const kpiNetMargin = document.getElementById('kpi-net-margin');

                kpiNetProfitUsd.textContent = (netProfitUsd >= 0 ? '+$' : '-$') + Math.abs(netProfitUsd).toFixed(2);
                kpiNetProfitCny.textContent = '≈ ' + (netProfitCny >= 0 ? '¥' : '-¥') + Math.abs(netProfitCny).toFixed(2);
                kpiNetMargin.textContent = netMarginPct.toFixed(1) + '%';

                if (netProfitUsd >= 0) {
                    cardNetProfit.className = 'kpi-card highlight-profit';
                    badgeProfitStatus.style.background = '#dcfce7';
                    badgeProfitStatus.style.color = '#166534';
                    badgeProfitStatus.style.borderColor = '#86efac';
                    badgeProfitStatus.textContent = '今日盈利';
                    kpiNetProfitUsd.style.color = '#15803d';
                } else {
                    cardNetProfit.className = 'kpi-card highlight-loss';
                    badgeProfitStatus.style.background = '#ffe4e6';
                    badgeProfitStatus.style.color = '#9f1239';
                    badgeProfitStatus.style.borderColor = '#fecdd3';
                    badgeProfitStatus.textContent = '今日亏损';
                    kpiNetProfitUsd.style.color = '#e11d48';
                }

                document.getElementById('kpi-roas').textContent = realRoas.toFixed(2);
                document.getElementById('kpi-breakeven-roas').textContent = breakevenRoas > 0 ? breakevenRoas.toFixed(2) + 'x' : '--';

                document.getElementById('kpi-gross-revenue').textContent = '$' + grossRevenueUsd.toFixed(2);
                document.getElementById('kpi-gross-revenue-cny').textContent = '≈ ¥' + (grossRevenueUsd * exchangeRate).toFixed(2);
                document.getElementById('kpi-aov').textContent = '$' + aovUsd.toFixed(2);

                document.getElementById('kpi-cpa').textContent = '$' + cpaUsd.toFixed(2);
                document.getElementById('kpi-order-count-tag').textContent = ordersCount + ' 笔有效订单';
                document.getElementById('kpi-total-items').textContent = totalItemsCount + ' 件';

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

                if (missingSkus.size > 0) {
                    missingSkusAlert.style.display = 'block';
                    missingSkusCount.textContent = missingSkus.size;
                    renderMissingSkusInputs(Array.from(missingSkus));
                } else {
                    missingSkusAlert.style.display = 'none';
                }

                parsedSkuSummary = Array.from(skuMap.values()).map(s => {
                    const purchaseUsd = s.total_purchase_cny / exchangeRate;
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
                    card.style.background = '#ffffff';
                    card.style.border = '1px solid #fde68a';
                    card.style.borderRadius = '8px';
                    card.style.padding = '0.65rem';
                    const currentVal = customOverrides[sku] || { purchase_price: '', weight_g: '' };

                    card.innerHTML = `
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                            <span style="font-family:monospace;font-weight:700;font-size:0.75rem;color:#1e293b;" title="${sku}">${sku}</span>
                            <span style="font-size:0.625rem;background:#fef3c7;color:#92400e;padding:0.1rem 0.35rem;border-radius:4px;">待补齐</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.4rem;">
                            <div>
                                <label style="display:block;font-size:0.6875rem;color:#64748b;margin-bottom:0.15rem;">采购价(¥)</label>
                                <input type="number" step="0.1" placeholder="0.00" value="${currentVal.purchase_price ?? ''}"
                                       data-sku="${sku}" data-field="purchase_price" class="missing-sku-input param-input" style="padding:0.25rem 0.4rem;font-size:0.75rem;" />
                            </div>
                            <div>
                                <label style="display:block;font-size:0.6875rem;color:#64748b;margin-bottom:0.15rem;">重量(g)</label>
                                <input type="number" step="1" placeholder="100" value="${currentVal.weight_g ?? ''}"
                                       data-sku="${sku}" data-field="weight_g" class="missing-sku-input param-input" style="padding:0.25rem 0.4rem;font-size:0.75rem;" />
                            </div>
                        </div>
                    `;
                    missingSkusList.appendChild(card);
                });

                missingSkusList.querySelectorAll('.missing-sku-input').forEach(input => {
                    input.addEventListener('input', function () {
                        const sku = this.dataset.sku;
                        const field = this.dataset.field;
                        const val = parseFloat(this.value);

                        if (!customOverrides[sku]) {
                            customOverrides[sku] = { purchase_price: null, weight_g: null };
                        }
                        customOverrides[sku][field] = isNaN(val) ? null : val;

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
                    tbodySkus.innerHTML = `<tr><td colspan="10" style="padding:2.5rem 1rem;text-align:center;color:#94a3b8;">没有找到匹配的 SKU 数据</td></tr>`;
                    return;
                }

                filtered.sort((a, b) => b.total_quantity - a.total_quantity);

                tbodySkus.innerHTML = filtered.map(item => {
                    const isProfit = item.gross_profit_usd >= 0;
                    return `
                        <tr>
                            <td style="font-family:monospace;font-weight:700;color:#1e293b;white-space:nowrap;">
                                ${item.sku}
                                ${!item.is_erp ? '<span style="margin-left:0.25rem;font-size:0.625rem;color:#d97706;background:#fef3c7;padding:0.1rem 0.3rem;border-radius:4px;border:1px solid #fde68a;">自定义</span>' : ''}
                            </td>
                            <td>
                                <div style="font-weight:600;color:#1e293b;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${item.name}">${item.name || '--'}</div>
                                <div style="font-size:0.6875rem;color:#94a3b8;">${item.project_name || '未关联项目'}</div>
                            </td>
                            <td style="text-align:center;font-weight:700;color:#0f766e;">${item.total_quantity}</td>
                            <td style="text-align:right;font-weight:600;color:#1e293b;">$${item.total_revenue_usd.toFixed(2)}</td>
                            <td style="text-align:right;color:#64748b;">¥${item.purchase_price ? item.purchase_price.toFixed(2) : '0.00'}</td>
                            <td style="text-align:right;color:#64748b;">${item.weight_g || 0}g</td>
                            <td style="text-align:right;color:#64748b;">$${item.purchase_usd.toFixed(2)}</td>
                            <td style="text-align:right;color:#64748b;">$${item.shipping_usd.toFixed(2)}</td>
                            <td style="text-align:right;font-weight:700;color:${isProfit ? '#15803d' : '#e11d48'};">
                                ${isProfit ? '+' : ''}$${item.gross_profit_usd.toFixed(2)}
                            </td>
                            <td style="text-align:right;font-weight:600;color:${isProfit ? '#166534' : '#e11d48'};">
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
                    tbodyOrders.innerHTML = `<tr><td colspan="9" style="padding:2.5rem 1rem;text-align:center;color:#94a3b8;">没有找到匹配的订单</td></tr>`;
                    return;
                }

                tbodyOrders.innerHTML = filtered.map(ord => {
                    const isProfit = ord.gross_profit_usd >= 0;
                    const itemsDesc = ord.items.map(i => `${i.sku} × ${i.quantity}`).join(', ');

                    return `
                        <tr>
                            <td style="font-family:monospace;font-weight:700;color:#0f766e;white-space:nowrap;">${ord.name}</td>
                            <td style="color:#64748b;white-space:nowrap;">${ord.created_at || '--'}</td>
                            <td style="text-align:center;">
                                <span class="badge-status ${ord.financial_status === 'paid' ? 'paid' : 'other'}">
                                    ${ord.financial_status}
                                </span>
                            </td>
                            <td style="color:#334155;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${itemsDesc}">
                                <strong style="color:#1e293b;">${ord.items_count} 件:</strong> ${itemsDesc}
                            </td>
                            <td style="text-align:right;font-weight:600;color:#1e293b;">$${ord.revenue_usd.toFixed(2)}</td>
                            <td style="text-align:right;color:#64748b;">$${ord.purchase_usd.toFixed(2)}</td>
                            <td style="text-align:right;color:#64748b;">$${ord.shipping_usd.toFixed(2)}</td>
                            <td style="text-align:right;color:#94a3b8;">$${ord.gateway_usd.toFixed(2)}</td>
                            <td style="text-align:right;font-weight:700;color:${isProfit ? '#15803d' : '#e11d48'};">
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

                let csv = '\uFEFF';
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

            // 交互事件绑定
            fileInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    handleFileUpload(e.target.files[0]);
                }
            });

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
                uploadBadge.style.display = 'none';
                fileInfoText.textContent = '暂未上传文件';
                inputFbAdSpend.value = '0.00';
                btnExportCsv.disabled = true;
                calculateAndRender();
            });

            btnExportCsv.addEventListener('click', exportCsvReport);

            [inputFbAdSpend, paramExchangeRate, paramShippingBase, paramShippingRate, paramPaymentFeeRate].forEach(el => {
                el.addEventListener('input', calculateAndRender);
            });

            filterStatus.addEventListener('change', calculateAndRender);

            tableSearch.addEventListener('input', function (e) {
                currentSearchTerm = e.target.value.trim();
                renderSkuTable(parsedSkuSummary);
                renderOrderTable(parsedOrders);
            });

            tabBtnSkus.addEventListener('click', function () {
                activeTab = 'skus';
                tabBtnSkus.className = 'table-tab-btn active';
                tabBtnOrders.className = 'table-tab-btn';
                viewSkus.style.display = 'block';
                viewOrders.style.display = 'none';
            });

            tabBtnOrders.addEventListener('click', function () {
                activeTab = 'orders';
                tabBtnOrders.className = 'table-tab-btn active';
                tabBtnSkus.className = 'table-tab-btn';
                viewOrders.style.display = 'block';
                viewSkus.style.display = 'none';
            });
        })();
    </script>
</x-layouts.app>
