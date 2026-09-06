<x-layouts.app :title="($departmentWorkspace['title'] ?? '产品项目').' · NC ERP'">
    @php
        $stage = $filters['stage'] ?? null;
        $userStage = auth()->user()?->department?->code;
        $canEdit = auth()->user()?->hasRole('administrator') || $userStage === $stage;
        // Administrators manage the department workspace currently open; members manage only their own.
        $feedbackTargetStage = auth()->user()?->hasRole('administrator') ? $stage : $userStage;
        $labels = ['market_research' => '产品部', 'website_operations' => '运营部', 'content_creative' => '创意部', 'traffic_growth' => '流量部'];
        $sourceLabels = ['tiktok' => 'TikTok', 'facebook_ads' => 'Facebook 广告库', 'amazon' => 'Amazon', 'independent_store' => '独立站'];
    @endphp
    <div class="mb-7"><p class="text-sm font-semibold dept-text-{{ $stage }}">{{ $departmentWorkspace['eyebrow'] ?? '产品项目' }}</p><h1 class="mt-1 text-3xl font-bold">{{ $departmentWorkspace['title'] ?? '产品项目池' }}</h1><p class="mt-2 text-sm text-slate-500">{{ $departmentWorkspace['description'] ?? '统一查看产品与协作进度。' }}</p></div>

    @if($departmentWorkspace && $departmentWorkspace['allows_create'] && $canEdit)
        <section id="create-project" class="mb-6 rounded-xl border dept-panel-market_research p-5">
            <div><h2 class="text-base font-bold">手动创建产品项目</h2><p class="mt-1 text-sm text-slate-600">系统默认服务美国市场。产品图片、类目和产品阶段会成为全流程标签，供四个部门共同识别与筛选。</p></div>
            <form method="POST" enctype="multipart/form-data" action="{{ route('projects.store') }}" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                @csrf
                <label class="field-label">产品名称 <span>*</span><input name="product_name" required placeholder="例如：便携果蔬清洗杯" class="field-input"></label>
                <div class="field-label">产品类目 <span>*</span><select id="project-category" name="category" required class="field-input"><option value="">请选择产品类目</option>@foreach($availableCategories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach<option value="__manage__">＋ 管理产品类目…</option></select><small>选择“管理产品类目”可在此处直接新增或删除。</small><div id="category-manager" hidden class="mt-2 rounded-lg border border-orange-300 bg-white p-3"><p class="text-xs font-bold text-orange-900">产品类目管理（仅产品部）</p><div class="mt-2 flex flex-wrap gap-2">@foreach($managedCategories as $managedCategory)<div class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs"><span>{{ $managedCategory->name }}</span><button type="button" class="text-red-700" aria-label="删除 {{ $managedCategory->name }}">删除</button></div>@endforeach</div><div class="mt-3 flex gap-2"><input name="name" placeholder="新增产品类目" class="field-input mt-0"><button type="button" data-add-category class="rounded bg-orange-700 px-3 text-sm font-semibold text-white">添加</button></div></div></div>
                <label class="field-label">产品阶段 <span>*</span><select name="priority" required class="field-input"><option value="" selected disabled>请选择产品阶段</option><option value="initial_screening">初筛产品</option><option value="market_new">市场新品</option><option value="historical_winner">历史爆品</option></select></label>
                <label class="field-label">创建时间 <input name="created_at_business" type="datetime-local" value="{{ now()->format('Y-m-d\\TH:i') }}" class="field-input"><small>用于项目时间筛选；未调整时默认使用当前时间。</small></label>
                <label class="field-label">产品主图 <span>*</span><input name="product_image" required type="file" accept="image/png,image/jpeg,image/webp" class="field-input"><small>仅需上传一张，用于所有部门识别产品。</small></label>
                <div class="xl:col-span-5"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">创建产品并开始选品</button></div>
            </form>
        </section>
    @endif

    <form method="GET" class="mb-5 rounded-xl border border-slate-200 bg-white p-4"><input type="hidden" name="stage" value="{{ $stage }}"><div class="grid gap-4 xl:grid-cols-[minmax(250px,0.95fr)_minmax(0,2.65fr)]"><label class="rounded-xl border dept-panel-{{ $stage }} p-3 text-xs font-semibold">当前产品项目<select name="project" onchange="this.form.submit()" class="mt-2 w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800"><option value="">选择一个产品项目</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected($selectedProject?->id === $project->id)>{{ $project->product_name }} · {{ $project->project_code }}</option>@endforeach</select></label><div class="grid gap-3 border-slate-200 xl:border-l xl:pl-4 md:grid-cols-2 xl:grid-cols-[minmax(190px,1.25fr)_minmax(150px,0.75fr)_minmax(130px,0.6fr)_minmax(135px,0.7fr)_minmax(135px,0.7fr)_auto_auto]"><label class="text-xs font-medium text-slate-600">搜索项目<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="产品名称或项目编号" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label><label class="text-xs font-medium text-slate-600">产品类目<select name="category" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">全部类目</option>@foreach($availableCategories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? null) === $category)>{{ $category }}</option>@endforeach</select></label><label class="text-xs font-medium text-slate-600">产品阶段<select name="priority" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">全部阶段</option><option value="initial_screening" @selected(($filters['priority'] ?? null) === 'initial_screening')>初筛产品</option><option value="market_new" @selected(($filters['priority'] ?? null) === 'market_new')>市场新品</option><option value="historical_winner" @selected(($filters['priority'] ?? null) === 'historical_winner')>历史爆品</option></select></label><label class="text-xs font-medium text-slate-600">创建时间从<input name="created_from" type="date" value="{{ $filters['created_from'] ?? '' }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label><label class="text-xs font-medium text-slate-600">创建时间至<input name="created_to" type="date" value="{{ $filters['created_to'] ?? '' }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label><button class="mt-5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">应用筛选</button><a href="{{ route('projects.index', ['stage' => $stage]) }}" class="mt-5 rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">清除</a></div></div>@if($projects->isEmpty())<p class="mt-3 text-sm text-slate-500">当前没有可处理项目。</p>@endif</form>

    <div id="project-work-area"></div>
    @if($departmentWorkspace && $selectedProject)
        <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5">
            <h3 class="font-semibold">项目四部门总览</h3>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-sm">
                <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">产品部</p><p class="mt-1 text-slate-600">选品证据 {{ $selectedProject->researchSources->count() }} 条 · SKU {{ $selectedProject->skus->count() }} 个</p></div>
                <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">运营部</p><p class="mt-1 text-slate-600">1688 货源 {{ $selectedProject->sources->count() }} 条 · Shopify {{ $selectedProject->landingPages->count() }} 页</p></div>
                <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">创意部</p><p class="mt-1 text-slate-600">素材 {{ $selectedProject->creativeAssets->count() }} 个</p></div>
                <div class="rounded-lg bg-slate-50 p-3"><p class="font-medium">流量部</p><p class="mt-1 text-slate-600">投放 {{ $selectedProject->campaignTests->count() }} 条 · 待处理反馈 {{ $selectedProject->optimizationFeedback->where('status', '!=', 'resolved')->count() }} 条</p></div>
            </div>
        </section>
        @php
            $videos = $selectedProject->creativeAssets->filter(fn ($asset) => in_array('video', $asset->asset_types ?? [$asset->asset_type], true));
            $incoming = $selectedProject->decisions->where('requested_from_stage', $stage)->where('status', 'open');
            $openSpecificationDecision = $incoming->first(fn ($decision) => $decision->decision_type === 'specification');
            $pendingOperationsSpecifications = $selectedProject->decisions->where('decision_type', 'specification')->where('requested_from_stage', 'website_operations')->where('status', 'resolved')->flatMap(fn ($decision) => data_get($decision->details, 'requested_specifications', []))->map(fn ($specification) => trim((string) $specification))->filter()->unique()->reject(fn ($specification) => $selectedProject->skus->contains(fn ($sku) => $sku->variant_name === $specification))->values();
        @endphp
            @php
                $projectStages = [
                    'market_research' => ['label' => '产品部', 'description' => '选品、产品规格与货源信息确认'],
                    'website_operations' => ['label' => '运营部', 'description' => '货源核验、Shopify 页面与产品信息完善'],
                    'content_creative' => ['label' => '创意部', 'description' => '视频素材与详情页动图制作'],
                    'traffic_growth' => ['label' => '流量部', 'description' => '广告投放、数据复盘与协作反馈'],
                ];
                $currentStageIndex = array_search($selectedProject->current_stage, array_keys($projectStages), true);
            @endphp
            <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <header class="project-summary-header border-b border-slate-100 pb-5">
                    <div class="project-summary-title">
                        <p class="text-xs font-semibold dept-text-{{ $stage }}">{{ $selectedProject->project_code }}@if($selectedProject->released_at) · 发布于 {{ $selectedProject->released_at->format('Y-m-d') }}@endif</p>
                        <div class="mt-2 flex items-center gap-3">
                            @if($selectedProject->product_image_path)<img src="{{ asset('storage/'.$selectedProject->product_image_path) }}" alt="{{ $selectedProject->product_name }} 产品主图" class="size-12 shrink-0 rounded-lg object-cover">@endif
                            <div><h2 class="text-2xl font-bold">{{ $selectedProject->product_name }}</h2><p class="mt-1 text-sm text-slate-500">当前环节：{{ $labels[$selectedProject->current_stage] ?? $selectedProject->current_stage }}</p></div>
                        </div>
                    </div>
                    <section class="project-progress-rail" data-project-stage-progress aria-label="项目进程">
                        <div class="project-progress-meta"><p>项目进程</p><x-status-badge :status="$selectedProject->status" /></div>
                        <div class="project-progress-steps" role="list">
                            @foreach($projectStages as $stageCode => $stageMeta)
                                @php $stageIndex = array_search($stageCode, array_keys($projectStages), true); @endphp
                                <button type="button" role="listitem" data-project-stage-item data-stage-label="{{ $stageMeta['label'] }}" data-stage-description="{{ $stageMeta['description'] }}" aria-pressed="{{ $stageCode === $selectedProject->current_stage ? 'true' : 'false' }}" @class(['project-progress-step', 'is-complete' => $stageIndex < $currentStageIndex, 'is-current' => $stageCode === $selectedProject->current_stage])>
                                    <span class="project-progress-dot">{{ $stageIndex + 1 }}</span><span>{{ $stageMeta['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <p class="project-progress-detail" data-project-stage-description>当前环节：{{ $labels[$selectedProject->current_stage] ?? $selectedProject->current_stage }} · {{ data_get($projectStages, $selectedProject->current_stage.'.description') }}</p>
                    </section>
                </header>
            @if(! $canEdit)<p class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">当前以 {{ $labels[$userStage] ?? '其他部门' }} 身份查看；本部门工作与待处理事项会优先显示，完整项目资料见下方。</p>@endif
            <div class="mt-5"><div class="w-full rounded-xl border dept-panel-{{ $stage }} p-5">
                @if($stage === 'market_research')
                    <p class="text-sm font-semibold dept-text-market_research">产品部重点工作</p><h3 class="mt-2 text-lg font-bold">选品、产品规格与货源产品信息</h3><div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(280px,1.25fr)]"><div><?php $selectionEvidence = $selectedProject->researchSources->first(); ?><p class="text-sm font-semibold">当前选品信息</p><?php if($canEdit): ?><form method="POST" action="{{ route('projects.research-sources.store', $selectedProject) }}" class="mt-2 space-y-3 rounded-lg border border-orange-200 bg-white p-4">@csrf<label class="field-label">来源平台 <span>*</span><select name="platform" required class="field-input"><option value="tiktok" @selected(old('platform', $selectionEvidence?->platform) === 'tiktok')>TikTok</option><option value="facebook_ads" @selected(old('platform', $selectionEvidence?->platform) === 'facebook_ads')>Facebook 广告库</option><option value="amazon" @selected(old('platform', $selectionEvidence?->platform) === 'amazon')>Amazon</option><option value="independent_store" @selected(old('platform', $selectionEvidence?->platform) === 'independent_store')>独立站</option></select></label><label class="field-label">证据链接 <span>*</span><input name="url" required value="{{ old('url', $selectionEvidence?->url) }}" placeholder="粘贴可验证的链接" class="field-input"></label><label class="field-label">选品理由 <span>*</span><textarea name="evidence_note" required placeholder="说明需求、价格、竞争或广告信号" class="field-input">{{ old('evidence_note', $selectionEvidence?->evidence_note) }}</textarea></label><button class="rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white">{{ $selectionEvidence ? '更新选品信息' : '保存选品信息' }}</button></form><?php else: ?><div class="mt-2 rounded-lg border border-orange-100 bg-white p-4 text-sm"><p class="font-semibold text-orange-900">{{ $selectionEvidence?->custom_source_name ?: ($sourceLabels[$selectionEvidence?->platform] ?? '暂未填写') }}</p><a href="{{ $selectionEvidence?->url }}" target="_blank" rel="noreferrer" class="mt-1 block break-all font-medium text-blue-700 underline">{{ $selectionEvidence?->url }}</a><p class="mt-2 text-slate-700">{{ $selectionEvidence?->evidence_note }}</p></div><?php endif; ?></div><figure data-image-update-action="{{ route('projects.image.update', $selectedProject) }}" class="overflow-hidden rounded-xl border border-orange-200 bg-white p-3"><figcaption class="mb-2 text-sm font-semibold text-orange-900">产品主图</figcaption>@if($selectedProject->product_image_path)<img src="{{ asset('storage/'.$selectedProject->product_image_path) }}" alt="{{ $selectedProject->product_name }} 产品主图" class="h-64 w-full rounded-lg object-contain">@else<div class="flex h-64 items-center justify-center rounded-lg bg-orange-50 text-sm text-orange-800">暂未上传产品主图</div>@endif</figure></div>
                    @if($canEdit)<div class="mt-4 grid gap-3 lg:grid-cols-3"><form method="POST" action="{{ route('projects.research-sources.store', $selectedProject) }}" class="space-y-2 rounded-lg border border-orange-200 bg-white p-3">@csrf<p class="text-sm font-semibold">添加选品证据</p><label class="field-label">来源平台 <span>*</span><select name="platform" required class="field-input"><option value="tiktok">TikTok</option><option value="facebook_ads">Facebook 广告库</option><option value="amazon">Amazon</option><option value="independent_store">独立站</option></select></label><label class="field-label">证据链接 <span>*</span><input name="url" required placeholder="粘贴可验证的链接" class="field-input"></label><label class="field-label">选品理由 <span>*</span><textarea name="evidence_note" required placeholder="说明需求、价格、竞争或广告信号" class="field-input"></textarea></label><button class="rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white">添加选品证据</button></form><form method="POST" action="{{ route('projects.skus.store', $selectedProject) }}" class="space-y-2 rounded-lg border border-orange-200 bg-white p-3">@csrf<p class="text-sm font-semibold">开发公司内部 SKU</p><p class="text-xs text-slate-500">先录入 1688 货源和产品规格，再从公司内部系统生成 SKU。</p><label class="field-label">公司内部 SKU <span>*</span><input name="sku_code" required placeholder="例如：SKU-US-001" class="field-input"></label><label class="field-label">产品规格 <span>*</span><input name="variant_name" required placeholder="例如：单件 / 两件套" class="field-input"></label><button class="rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white">回填内部 SKU</button></form><form method="POST" action="{{ route('projects.sources.store', $selectedProject) }}" class="space-y-2 rounded-lg border border-orange-200 bg-white p-3">@csrf<p class="text-sm font-semibold">录入 1688 货源与对应产品规格</p><label class="field-label">1688 货源链接 <span>*</span><input name="supplier_url" required placeholder="粘贴 1688 商品链接" class="field-input"></label><label class="field-label">供应商 / 产品名称 <span>*</span><input name="supplier_name" required placeholder="用于定位货源" class="field-input"></label><label class="field-label">公司内部 SKU <span>*</span><input name="sku_code" required placeholder="根据产品规格从公司内部系统生成" class="field-input"></label><label class="field-label">产品规格 <span>*</span><input name="variant_name" required placeholder="例如：单件 / 两件套 / 尺寸版本" class="field-input"></label><label class="field-label">采购价（CNY）<input name="purchase_price" type="number" step="0.01" min="0" placeholder="可后补" class="field-input"></label><input name="currency" value="CNY" type="hidden"><label class="field-label">货源说明 <span>*</span><textarea name="notes" required placeholder="供货、起订量、尺寸或注意事项" class="field-input"></textarea></label><button class="rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white">保存货源、规格与内部 SKU</button></form></div>@endif
                    @if($canEdit)<section class="mt-4 rounded-xl border border-orange-300 bg-white p-4"><p class="text-sm font-semibold text-orange-900">产品部执行顺序</p><h4 class="mt-1 font-semibold">1. 录入 1688 货源与产品规格　→　2. 回填公司内部 SKU　→　3. 发送运营部确认</h4><p class="mt-1 text-sm text-slate-600">先从 1688 核对货源与规格；根据规格在公司内部系统生成 SKU 后填写。保存后即可将确认需求发送给运营部。</p><form method="POST" action="{{ route('projects.sources.store', $selectedProject) }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">@csrf<label class="field-label xl:col-span-2">1688 货源链接 <span>*</span><input name="supplier_url" type="url" required placeholder="粘贴 1688 商品链接" class="field-input"></label><label class="field-label">供应商 / 产品名称 <span>*</span><input name="supplier_name" required placeholder="用于定位货源" class="field-input"></label><label class="field-label">产品规格 <span>*</span><input name="variant_name" required placeholder="例如：单件 / 两件套 / 尺寸版本" class="field-input"></label><label class="field-label">公司内部 SKU <span>*</span><input name="sku_code" required placeholder="根据产品规格从公司内部系统生成" class="field-input"></label><label class="field-label">采购价（CNY）<input name="purchase_price" type="number" step="0.01" min="0" placeholder="例如：19.90" class="field-input"></label><label class="field-label">重量（g）<input name="weight_g" type="number" min="0" placeholder="例如：350" class="field-input"></label><input name="currency" value="CNY" type="hidden"><label class="field-label md:col-span-2 xl:col-span-3">货源说明 <span>*</span><textarea name="notes" required rows="2" placeholder="填写供货、起订量、尺寸或注意事项" class="field-input"></textarea></label><div class="xl:col-span-3"><button class="rounded bg-orange-700 px-4 py-2 text-sm font-semibold text-white">保存货源、规格与内部 SKU</button></div></form>@if($selectedProject->skus->isNotEmpty())<form method="POST" action="{{ route('projects.decisions.store', $selectedProject) }}" class="mt-4 grid gap-3 border-t border-orange-200 pt-4 md:grid-cols-[1fr_2fr_auto]">@csrf<input type="hidden" name="decision_type" value="specification"><input type="hidden" name="requested_from_stage" value="website_operations"><input name="title" required value="确认路西法产品规格" placeholder="请运营部确认的事项" class="field-input mt-0"><input name="details" required placeholder="说明内部 SKU、产品规格及需确认的问题" class="field-input mt-0"><button class="rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white">发送运营部确认</button></form>@else<p class="mt-4 rounded-lg bg-orange-50 p-3 text-sm text-orange-900">完成第 1、2 步后，这里会出现“发送运营部确认”入口。</p>@endif</section>@endif
                @if($canEdit)
                    @php $productSource = $selectedProject->sources->first(); @endphp
                    <section data-product-source-editor class="mt-4 rounded-xl border border-orange-300 bg-white p-5">
                        <p class="text-sm font-semibold text-orange-800">1688 货源与产品规格</p>
                        <h4 class="mt-1 text-lg font-bold">货源信息与多规格录入</h4>
                        <p class="mt-1 text-sm text-slate-600">可维护多个供应商货源；每个产品规格单独填写内部 SKU、采购价和重量。</p>
                        @if($selectedProject->skus->isNotEmpty())
                            <section class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-950">当前已录入的产品规格</p>
                                <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach($selectedProject->skus as $sku)
                                        <article class="rounded-lg border border-slate-200 bg-white p-3 text-sm">
                                            <p class="font-semibold text-slate-950">{{ $sku->variant_name }}</p>
                                            <p class="mt-1 text-slate-600">内部 SKU：{{ $sku->sku_code ?: '待生成' }}</p>
                                            <p class="mt-1 text-slate-500">采购价 ¥{{ $sku->purchase_price ?? '待补' }} · 重量 {{ $sku->weight_g ?? '待补' }}g</p>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if($selectedProject->sources->isNotEmpty())
                            <section class="mt-4 rounded-xl border border-orange-200 bg-orange-50 p-4">
                                <p class="font-semibold text-orange-950">当前供应商货源</p>
                                <div class="mt-3 grid gap-2 md:grid-cols-2">
                                    @foreach($selectedProject->sources as $source)
                                        <article class="rounded-lg border border-orange-100 bg-white p-3 text-sm"><p class="font-semibold text-slate-900">{{ $source->supplier_name }}</p><a href="{{ $source->supplier_url }}" target="_blank" rel="noreferrer" class="mt-1 block break-all text-blue-700 underline">{{ $source->supplier_url }}</a></article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if($pendingOperationsSpecifications->isNotEmpty())
                            <section class="mt-4 rounded-xl border p-4 shadow-sm" style="background-color:#1e3a8a !important;border-color:#172554 !important;color:#ffffff !important;">
                                <p class="font-semibold">运营部新增规格待开发</p>
                                <p class="mt-1 text-sm" style="color:#dbeafe !important;">请在下方为每项需求新增产品规格并填写对应的公司内部 SKU；保存后该需求会自动消失。</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($pendingOperationsSpecifications as $specification)
                                        <span class="rounded-full border px-3 py-1 text-sm font-semibold" style="background-color:#1e40af !important;border-color:#93c5fd !important;color:#ffffff !important;">{{ $specification }}</span>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        <form method="POST" action="{{ route('projects.sources.store', $selectedProject) }}" class="mt-4 space-y-4">
                            @csrf
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                <label class="field-label xl:col-span-2">1688 货源链接 <span>*</span><input name="supplier_url" type="url" required value="{{ old('supplier_url', $productSource?->supplier_url) }}" placeholder="粘贴 1688 商品链接" class="field-input"></label>
                                <label class="field-label">供应商名称 <span>*</span><input name="supplier_name" required value="{{ old('supplier_name', $productSource?->supplier_name) }}" placeholder="例如：义乌某某工厂" class="field-input"></label>
                                <label class="field-label">货源产品名称 <span>*</span><input name="product_name" required value="{{ old('product_name', $productSource?->product_name) }}" placeholder="填写 1688 上的产品名称" class="field-input"></label>
                                <label class="field-label md:col-span-2 xl:col-span-3">货源说明 <span>*</span><textarea name="notes" required rows="2" placeholder="填写供货、起订量、尺寸或注意事项" class="field-input">{{ old('notes', $productSource?->notes) }}</textarea></label>
                            </div>
                            <section class="border-t border-orange-200 pt-4"><div class="flex flex-wrap items-center justify-between gap-3"><div><p class="font-semibold">其他供应商货源</p><p class="text-sm text-slate-600">同一产品可补充多个备选商家的名称和 1688 货源地址。</p></div><button type="button" data-add-alternative-source class="rounded border border-orange-300 px-3 py-2 text-sm font-semibold text-orange-900">＋ 添加其他商家</button></div><div data-alternative-source-list class="mt-3 space-y-3"></div></section>
                            <div class="border-t border-orange-200 pt-4"><div class="flex items-center justify-between gap-3"><div><p class="font-semibold">产品规格与内部 SKU</p><p class="text-sm text-slate-600">每一行对应一个产品规格及其采购数据。</p></div><button type="button" data-add-specification class="rounded border border-orange-300 px-3 py-2 text-sm font-semibold text-orange-900">＋ 添加产品规格</button></div><div data-specification-list class="mt-3 space-y-3"></div></div>
                            <input name="currency" value="CNY" type="hidden">
                            <button class="rounded bg-orange-700 px-4 py-2 text-sm font-semibold text-white">保存货源与产品规格</button>
                        </form>
                    </section>
                @endif
                @elseif($stage === 'website_operations')
                    <p class="text-sm font-semibold dept-text-website_operations">运营部重点工作</p><h3 class="mt-2 text-lg font-bold">最终产品规格与 Shopify 产品</h3><p class="mt-3 text-sm text-slate-600">产品部已录入货源产品信息 {{ $selectedProject->sources->count() }} 条 · 产品规格 {{ $selectedProject->skus->count() }} 个 · Shopify 页面 {{ $selectedProject->landingPages->count() }} 个。运营部只确认或修改产品部发来的初步规格，再反馈产品部开发内部 SKU。</p>
                    @if($canEdit && $openSpecificationDecision)<section class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4"><div><h4 class="font-semibold text-blue-950">产品规格与内部 SKU</h4><p class="mt-1 text-sm text-blue-800">以下为产品部已确定的信息，只读展示；运营部可随时确认采用或提出新增规格需求。</p></div><div class="mt-3 grid gap-2 md:grid-cols-2">@forelse($selectedProject->skus as $sku)<div class="rounded-lg border border-blue-100 bg-white p-3 text-sm"><p class="font-semibold text-slate-900">{{ $sku->variant_name }}</p><p class="mt-1 text-blue-900">内部 SKU：{{ $sku->sku_code ?: '待产品部生成' }}</p><p class="mt-1 text-slate-600">采购价 {{ $sku->purchase_price !== null ? '¥'.number_format((float) $sku->purchase_price, 2) : '待补充' }} · 重量 {{ $sku->weight_g !== null ? $sku->weight_g.'g' : '待补充' }}</p><p class="mt-1 text-xs text-slate-400">提交于 {{ $sku->created_at?->format('Y-m-d H:i') }}</p></div>@empty<p class="rounded-lg bg-white p-3 text-sm text-slate-600">产品部暂未录入产品规格与内部 SKU。</p>@endforelse</div><div class="mt-4 flex flex-wrap gap-2"><form method="POST" action="{{ route('projects.decisions.respond', [$selectedProject, $openSpecificationDecision]) }}">@csrf @method('PATCH')<button name="specification_action" value="adopt" class="rounded bg-blue-700 px-4 py-2 text-sm font-semibold text-white">确认采用并反馈产品部</button></form><button type="button" data-open-specification-request class="rounded border border-blue-300 bg-white px-4 py-2 text-sm font-semibold text-blue-800">需要新增规格</button></div><form data-specification-request-form hidden method="POST" action="{{ route('projects.decisions.respond', [$selectedProject, $openSpecificationDecision]) }}" class="mt-3 space-y-3 rounded-lg border border-blue-200 bg-white p-3">@csrf @method('PATCH')<div class="flex items-center justify-between gap-3"><p class="font-semibold text-blue-950">运营部新增产品规格需求</p><button type="button" data-add-specification-request class="rounded border border-blue-300 px-3 py-1 text-sm font-semibold text-blue-800">＋ 添加产品规格</button></div><div data-specification-request-list class="space-y-2"><div class="flex gap-2"><input name="requested_specifications[]" required placeholder="例如：两件套 / 礼盒装 / 新尺寸版本" class="field-input min-w-0 flex-1"><button type="button" data-remove-specification-request class="rounded border border-blue-200 px-3 text-sm text-blue-800">删除</button></div></div><button name="specification_action" value="request" class="rounded bg-blue-700 px-4 py-2 text-sm font-semibold text-white">发送新增规格给产品部生成内部 SKU</button></form></section>@endif
                    @if($canEdit)<div class="mt-4 space-y-4">@if($selectedProject->landingPages->isNotEmpty())<section class="rounded-lg border border-blue-200 bg-blue-50 p-4"><div><p class="text-sm font-semibold text-blue-950">已保存的 Shopify 页面</p><p class="mt-1 text-sm text-blue-800">以下内容会直接供产品部、创意部和流量部查看。</p></div><div class="mt-3 space-y-3">@foreach($selectedProject->landingPages as $page)<article class="grid overflow-hidden rounded-lg border border-blue-100 bg-white md:grid-cols-[minmax(0,1fr)_minmax(280px,0.9fr)]"><div class="flex min-w-0 flex-col justify-center p-4"><p class="font-semibold text-slate-900">{{ $page->title }}</p><p class="mt-1 text-xs text-slate-400">提交于 {{ $page->created_at?->format('Y-m-d H:i') }}</p><p class="mt-3 text-xs font-medium text-slate-500">Shopify 产品或落地页链接</p><a href="{{ $page->page_url }}" target="_blank" rel="noreferrer" class="mt-1 block break-all text-sm font-medium text-blue-700 underline">{{ $page->page_url }}</a></div><div class="border-t border-blue-100 bg-slate-50 p-3 md:border-l md:border-t-0"><p class="mb-2 text-sm font-semibold text-slate-700">详情页预览</p>@if($page->detail_image_path)<img src="{{ asset('storage/'.$page->detail_image_path) }}" alt="{{ $page->title }}详情页图片" class="h-64 w-full rounded-md bg-white object-contain">@else<div class="flex h-64 items-center justify-center rounded-md bg-white text-sm text-slate-500">暂未上传详情页图片</div>@endif</div></article>@endforeach</div></section>@endif<form method="POST" enctype="multipart/form-data" action="{{ route('projects.landing-pages.store', $selectedProject) }}" class="space-y-3 rounded-lg border border-blue-200 bg-white p-3">@csrf<p class="text-sm font-semibold">Shopify 产品与详情页</p><label class="field-label">Shopify 产品或落地页链接 <span>*</span><input name="page_url" required placeholder="粘贴 Shopify 页面链接" class="field-input"></label><p class="rounded-md bg-blue-50 px-3 py-2 text-sm text-blue-800">该落地页自动关联「{{ $selectedProject->product_name }}」的全部产品规格，无需重复选择。</p><label class="field-label">详情页图片 <span>*</span><input name="detail_image" required type="file" accept="image/png,image/jpeg,image/webp" class="field-input"><small>用于创意部、流量部和产品部查阅。</small></label><button class="rounded bg-blue-700 px-3 py-2 text-sm font-semibold text-white">保存 Shopify 页面</button></form></div>@endif
                @elseif($stage === 'content_creative')
                    <p class="text-sm font-semibold text-blue-900">创意部重点工作</p><h3 class="mt-2 text-lg font-bold">素材清单与上传</h3><p class="mt-3 text-sm text-slate-600">已有 {{ $selectedProject->creativeAssets->count() }} 个素材；参考产品、网站 SKU 和投放反馈制作素材。</p>
                    @if($canEdit)<form method="POST" enctype="multipart/form-data" action="{{ route('projects.creative-assets.store', $selectedProject) }}" class="mt-4 grid gap-3 rounded-lg border border-yellow-200 bg-white p-4 md:grid-cols-2">@csrf<div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3"><p class="text-xs font-semibold text-yellow-800">本次素材名称</p><p class="mt-1 text-lg font-bold text-yellow-950">{{ $selectedProject->product_name }}</p><p class="mt-1 text-xs text-yellow-800">系统会自动使用产品标题作为素材名称。</p></div><div class="rounded-lg border border-yellow-200 p-3 text-sm"><p class="font-medium">素材类型 <span class="text-red-700">*</span></p><p class="mt-1 text-xs text-slate-500">仅保留广告投放视频和详情页动图。</p><label class="mt-2 block"><input type="checkbox" name="asset_types[]" value="video"> 视频（广告投放）</label><label class="block"><input type="checkbox" name="asset_types[]" value="gif"> 动图（详情页）</label></div><label class="field-label">参考来源 <span>*</span><select name="source_type" class="field-input"><option value="tiktok">TikTok</option><option value="youtube">YouTube</option><option value="other">其他</option></select></label><label class="rounded border border-dashed border-yellow-300 p-3 text-sm text-slate-600">上传文件（视频或动图，可选）<input name="asset_file" type="file" accept="video/*,image/gif" class="mt-2 block w-full"></label><div class="rounded-lg border border-yellow-200 p-3 md:col-span-2"><div class="flex items-center justify-between gap-3"><p class="font-semibold">参考素材链接 <span class="text-red-700">*</span></p><button type="button" data-add-reference-url class="rounded border border-yellow-400 px-3 py-1 text-sm font-semibold text-yellow-900">＋ 添加链接</button></div><p class="mt-1 text-xs text-slate-500">填写本素材参考过的外部内容，可添加多条。</p><div data-reference-url-list class="mt-3 space-y-2"><div class="flex gap-2"><input name="reference_urls[]" type="url" required placeholder="粘贴参考素材链接" class="field-input mt-0 min-w-0 flex-1"><button type="button" data-remove-reference-url class="rounded border border-yellow-300 px-3 text-sm font-semibold text-yellow-900">删除</button></div></div></div><label class="field-label md:col-span-2">脚本或核心卖点 <span>*</span><textarea name="copy_text" required rows="3" placeholder="填写素材脚本、创意方向或核心卖点" class="field-input"></textarea></label><div class="md:col-span-2"><button class="rounded bg-yellow-500 px-3 py-2 text-sm font-semibold text-slate-950">保存素材</button></div></form>@endif
                @elseif($stage === 'traffic_growth')
                    <p class="text-sm font-semibold dept-text-traffic_growth">流量部重点工作</p><h3 class="mt-2 text-lg font-bold">Facebook 投放测试与反馈</h3><p class="mt-3 text-sm text-slate-600">可投视频 {{ $videos->count() }} 个 · Shopify 页面 {{ $selectedProject->landingPages->count() }} 个。填写原始广告数据后，系统自动计算 CTR、CPM、CPC、转化率、CPA、ROAS 和 CPR。</p>
                    <section class="mt-4 rounded-lg border border-violet-300 bg-violet-100 p-4"><div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-semibold text-violet-950">项目广告投放状态</p><p class="mt-1 text-sm text-violet-900">该状态独立于每次投放测试和反馈，用于告知各部门当前是否正在投放。</p></div><div class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-violet-950">@switch($selectedProject->ad_delivery_status ?? 'not_started')@case('active')投放中 @break @case('paused')已暂停 @break @case('ended')已结束 @break @default 未开启 @endswitch</div></div><p class="mt-3 text-sm text-violet-900">开始投放时间：{{ $selectedProject->ad_started_at?->format('Y-m-d H:i') ?? '尚未设置' }}</p>@if($canEdit)<form method="POST" action="{{ route('projects.ad-delivery-status.update', $selectedProject) }}" class="mt-3 grid gap-3 border-t border-violet-200 pt-3 md:grid-cols-2">@csrf @method('PATCH')<label class="field-label">广告投放状态 <span>*</span><select name="ad_delivery_status" required class="field-input"><option value="not_started" @selected(($selectedProject->ad_delivery_status ?? 'not_started') === 'not_started')>未开启</option><option value="active" @selected($selectedProject->ad_delivery_status === 'active')>投放中</option><option value="paused" @selected($selectedProject->ad_delivery_status === 'paused')>已暂停</option><option value="ended" @selected($selectedProject->ad_delivery_status === 'ended')>已结束</option></select></label><label class="field-label">开始投放时间<input name="ad_started_at" type="datetime-local" value="{{ $selectedProject->ad_started_at?->format('Y-m-d\\TH:i') }}" class="field-input"><small>未开启时可留空；其他状态需填写。</small></label><div class="md:col-span-2"><button class="rounded bg-violet-700 px-3 py-2 text-sm font-semibold text-white">保存投放状态</button></div></form>@endif</section>
                    @if($canEdit)<form method="POST" enctype="multipart/form-data" action="{{ route('projects.campaign-tests.store', $selectedProject) }}" class="mt-4 grid gap-3 rounded-lg border border-violet-200 bg-white p-3 md:grid-cols-2">@csrf<input type="hidden" name="platform" value="facebook"><div class="rounded border border-violet-200 bg-violet-50 px-3 py-2 text-sm font-medium text-violet-900">Facebook 广告（唯一投放渠道）</div><label class="field-label">广告系列名称 <span>*</span><input name="campaign_name" required placeholder="例如：US-产品-兴趣测试-01" class="field-input"></label><label class="field-label">本次投放使用的创意部视频素材 <span>*</span><select name="creative_asset_id" required class="field-input"><option value="">选择创意部视频素材</option>@foreach($videos as $asset)<option value="{{ $asset->id }}">{{ $asset->title }} · 创意部上传于 {{ $asset->created_at?->format('Y-m-d H:i') }}</option>@endforeach</select><small>此处直接读取当前产品项目中创意部上传的视频；保存后，投放记录会标明本次使用的素材。</small></label><label class="field-label">Shopify 页面 <span>*</span><select name="landing_page_id" required class="field-input"><option value="">选择 Shopify 页面</option>@foreach($selectedProject->landingPages as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach</select></label><label class="field-label">花费（USD） <span>*</span><input name="spend" type="number" step="0.01" min="0" required placeholder="例如：100.00" class="field-input"></label><label class="field-label">展示次数 <span>*</span><input name="impressions" type="number" min="0" required placeholder="用于自动计算 CPM / CTR" class="field-input"></label><label class="field-label">链接点击 <span>*</span><input name="clicks" type="number" min="0" required placeholder="用于自动计算 CPC / CTR" class="field-input"></label><label class="field-label">加车转化 <span>*</span><input name="add_to_cart_conversions" type="number" min="0" required class="field-input"></label><label class="field-label">结账转化 <span>*</span><input name="checkout_conversions" type="number" min="0" required class="field-input"></label><label class="field-label">购买数 <span>*</span><input name="purchase_conversions" type="number" min="0" required placeholder="用于自动计算 CPA / 转化率" class="field-input"></label><label class="field-label">购买金额（USD） <span>*</span><input name="purchase_value" type="number" step="0.01" min="0" required placeholder="用于自动计算 ROAS" class="field-input"></label><label class="field-label md:col-span-2">本次投放优化目标（用于计算 CPR） <span>*</span><select name="result_metric" required class="field-input"><option value="purchase">购买</option><option value="checkout">结账</option><option value="add_to_cart">加车</option><option value="click">链接点击</option></select><small>CPR 会按所选结果口径自动计算。</small></label><div data-paste-upload tabindex="0" class="rounded border border-dashed border-violet-300 p-3 text-sm text-slate-600 md:col-span-2"><p class="font-medium text-slate-700">广告投放详情截图</p><p class="mt-1">点击此处后按 Ctrl+V 粘贴截图，或选择文件上传。</p><input name="detail_image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full"><p data-paste-upload-status class="mt-1 text-xs text-violet-700"></p></div><div class="rounded border border-violet-200 p-3 text-sm md:col-span-2"><p class="font-medium">需要反馈的部门</p><label><input type="checkbox" name="feedback_target_stages[]" value="market_research"> 产品部</label><label class="ml-3"><input type="checkbox" name="feedback_target_stages[]" value="website_operations"> 运营部</label><label class="ml-3"><input type="checkbox" name="feedback_target_stages[]" value="content_creative"> 创意部</label></div><label class="field-label md:col-span-2">投放结论、反馈与调整事项 <span>*</span><textarea name="feedback_note" required rows="2" placeholder="说明结论，以及产品、页面、素材或规格需要如何调整" class="field-input"></textarea></label><div class="md:col-span-2"><button class="rounded bg-violet-700 px-3 py-2 text-sm font-semibold text-white">保存投放测试与反馈</button></div></form>@endif
                @endif
            </div><aside class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold">关联协作摘要</p><dl class="mt-4 space-y-3 text-sm text-slate-600"><div class="flex justify-between"><dt>内部 SKU</dt><dd>{{ $selectedProject->skus->count() }} 个</dd></div><div class="flex justify-between"><dt>详情页</dt><dd>{{ $selectedProject->landingPages->count() }} 个</dd></div><div class="flex justify-between"><dt>素材</dt><dd>{{ $selectedProject->creativeAssets->count() }} 个</dd></div><div class="flex justify-between"><dt>投放记录</dt><dd>{{ $selectedProject->campaignTests->count() }} 条</dd></div></dl>@if($incoming->isNotEmpty())<div class="mt-5 border-t pt-4"><p class="text-sm font-semibold text-amber-800">待处理协作请求</p>@foreach($incoming as $decision)<div class="mt-2 rounded-lg bg-amber-50 p-3 text-sm"><p class="font-medium">{{ $decision->title }}</p><p class="mt-1 text-amber-800">{{ data_get($decision->details, 'note') }}</p></div>@endforeach</div>@endif</aside></div>
            <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5"><div><h3 class="font-semibold">项目共享资料</h3><p class="mt-1 text-sm text-slate-500">四个部门均可查看。当前部门的重点工作已置顶，本区用于查阅其他部门的完整资料。</p></div><div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3"><article class="rounded-lg bg-slate-50 p-4 text-sm"><p class="font-semibold">产品部资料</p><p class="mt-2 text-slate-600">选品证据 {{ $selectedProject->researchSources->count() }} 条 · SKU {{ $selectedProject->skus->count() }} 个 · 1688 货源 {{ $selectedProject->sources->count() }} 条</p><div class="mt-3 space-y-2">@forelse($selectedProject->researchSources as $source)<div class="rounded bg-white p-3"><a href="{{ $source->url }}" target="_blank" rel="noreferrer" class="font-medium text-blue-700">{{ $source->custom_source_name ?: ($sourceLabels[$source->platform] ?? $source->platform) }}</a>@if($source->evidence_note)<p class="mt-1 text-slate-600">{{ $source->evidence_note }}</p>@endif</div>@empty<p class="text-slate-500">暂未录入选品证据。</p>@endforelse</div><div class="mt-3 border-t border-slate-200 pt-3">@forelse($selectedProject->skus as $sku)<p class="rounded bg-white p-3"><span class="font-medium">{{ $sku->sku_code }}</span> · {{ $sku->variant_name }}</p>@empty<p class="text-slate-500">暂未回填内部 SKU。</p>@endforelse</div><div class="mt-3 space-y-2 border-t border-slate-200 pt-3">@forelse($selectedProject->sources as $source)<div class="rounded bg-white p-3"><a href="{{ $source->supplier_url }}" target="_blank" rel="noreferrer" class="font-medium text-blue-700">{{ $source->supplier_name ?: '打开 1688 货源' }}</a><p class="mt-1 text-slate-500">{{ $source->purchase_price !== null ? $source->currency.' '.number_format((float) $source->purchase_price, 2) : '采购价待补充' }}{{ $source->weight_g ? ' · '.$source->weight_g.'g' : '' }}</p>@if($source->notes)<p class="mt-1 text-slate-600">{{ $source->notes }}</p>@endif</div>@empty<p class="text-slate-500">暂未录入 1688 货源产品信息。</p>@endforelse</div></article><article class="rounded-lg bg-slate-50 p-4 text-sm"><p class="font-semibold">运营部资料</p><p class="mt-2 text-slate-600">Shopify 产品/详情页 {{ $selectedProject->landingPages->count() }} 页</p><div class="mt-3 space-y-2">@forelse($selectedProject->landingPages as $page)<div class="rounded bg-white p-3"><a href="{{ $page->page_url }}" target="_blank" rel="noreferrer" class="font-medium text-blue-700">{{ $page->title }}</a><p class="mt-1 text-slate-500">关联 SKU {{ $page->skus->count() }} 个</p></div>@empty<p class="text-slate-500">暂未创建 Shopify 页面。</p>@endforelse</div></article><article class="rounded-lg bg-slate-50 p-4 text-sm"><p class="font-semibold">创意部资料</p><p class="mt-2 text-slate-600">素材 {{ $selectedProject->creativeAssets->count() }} 个</p><div class="mt-3 space-y-2">@forelse($selectedProject->creativeAssets as $asset)<div class="rounded bg-white p-3"><p class="font-medium">{{ $asset->title }}</p><p class="mt-1 text-slate-500">{{ implode('、', $asset->asset_types ?? [$asset->asset_type]) }} · {{ $asset->source_type }}</p>@if($asset->copy_text)<p class="mt-1 text-slate-600">{{ $asset->copy_text }}</p>@endif</div>@empty<p class="text-slate-500">暂未上传素材。</p>@endforelse</div></article></div></section>
            <section class="mt-5 rounded-xl border border-violet-200 bg-violet-50 p-5"><h3 class="font-semibold text-violet-950">共享投放数据与协作反馈</h3><p class="mt-1 text-sm text-violet-800">所有部门可查阅 Facebook 投放历史、结论与调整事项；本部门待处理反馈在下方直接处理。</p><div class="mt-3 grid gap-3 md:grid-cols-2">@forelse($selectedProject->campaignTests as $campaign)<article class="rounded-lg bg-white p-4 text-sm"><p class="font-semibold">{{ $campaign->campaign_name }}</p><p class="mt-1 text-violet-900">本次投放使用的创意部素材：{{ $campaign->creativeAsset?->title ?? '未关联素材' }}</p><p class="mt-1 text-slate-600">花费 ${{ number_format((float) $campaign->spend, 2) }} · 展示 {{ number_format((int) $campaign->impressions) }} · 点击 {{ number_format((int) $campaign->clicks) }}</p><p class="mt-1 text-slate-600">CTR {{ number_format((float) $campaign->ctr, 2) }}% · CPC ${{ $campaign->clicks ? number_format((float) $campaign->spend / $campaign->clicks, 2) : '—' }} · CPM ${{ $campaign->impressions ? number_format(((float) $campaign->spend / $campaign->impressions) * 1000, 2) : '—' }}</p><p class="mt-1 text-slate-600">加车 {{ $campaign->add_to_cart_conversions ?? 0 }} · 结账 {{ $campaign->checkout_conversions ?? 0 }}</p><div class="mt-2 border-t border-slate-100 pt-2">@forelse($campaign->revisions as $revision)<p class="text-xs text-slate-600">{{ $revision->created_at->format('Y-m-d H:i') }}：{{ $revision->conclusion }}；{{ $revision->adjustment_items }}</p>@empty<p class="text-xs text-slate-500">当前为初始记录，尚无调整版本。</p>@endforelse</div></article>@empty<p class="text-sm text-slate-500">暂无投放记录。</p>@endforelse</div></section>
            @if($stage === 'market_research' && $canEdit)<section class="mt-5 w-full rounded-xl border border-orange-300 bg-orange-50 p-5"><div><p class="text-sm font-semibold text-orange-900">产品部 · 货源与产品规格</p><h3 class="mt-1 text-lg font-bold">一个内部 SKU，对应一个产品规格与一组采购数据</h3><p class="mt-1 text-sm text-slate-600">1688 货源链接和供应商 / 产品名称会作为共用货源保存；同一货源下可持续新增不同产品规格与内部 SKU。</p></div><form method="POST" action="{{ route('projects.sources.store', $selectedProject) }}" class="mt-4 grid gap-4 rounded-xl border border-orange-200 bg-white p-4 md:grid-cols-2 xl:grid-cols-3">@csrf<label class="field-label xl:col-span-2">1688 货源链接 <span>*</span><input name="supplier_url" type="url" required placeholder="同一货源只需使用同一个链接" class="field-input"></label><label class="field-label">供应商 / 产品名称 <span>*</span><input name="supplier_name" required placeholder="同一货源下的 SKU 共用" class="field-input"></label><label class="field-label">公司内部 SKU <span>*</span><input name="sku_code" required placeholder="例如：SKU-US-001" class="field-input"></label><label class="field-label">产品规格 <span>*</span><input name="variant_name" required placeholder="例如：单件 / 两件套 / 12 影片版" class="field-input"></label><label class="field-label">该规格采购价（CNY）<input name="purchase_price" type="number" step="0.01" min="0" placeholder="例如：19.90" class="field-input"></label><label class="field-label">该规格重量（g）<input name="weight_g" type="number" min="0" placeholder="例如：350" class="field-input"></label><input name="currency" value="CNY" type="hidden"><label class="field-label md:col-span-2 xl:col-span-3">货源说明 <span>*</span><textarea name="notes" required placeholder="填写供货、起订量、尺寸或其他会影响这个产品规格的信息" class="field-input" rows="3"></textarea></label><div class="xl:col-span-3"><button class="rounded bg-orange-700 px-4 py-2 text-sm font-semibold text-white">保存货源与产品规格</button></div></form></section>@endif
            @if($stage === 'website_operations' && $canEdit && $incoming->isNotEmpty())<section class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4"><h3 class="font-semibold text-blue-950">待确认的初步产品规格</h3><p class="mt-1 text-sm text-blue-800">请选择采用初步规格，或填写修改后的最终产品规格并反馈给产品部。</p><div class="mt-3 space-y-3">@foreach($incoming as $decision)<article class="rounded-lg bg-white p-3 text-sm"><p class="font-medium">{{ $decision->title }}</p><p class="mt-1 text-slate-600">{{ data_get($decision->details, 'note') }}</p><form method="POST" action="{{ route('projects.decisions.respond', [$selectedProject, $decision]) }}" class="mt-3 flex gap-2">@csrf @method('PATCH')<input name="response_note" required placeholder="最终产品规格：采用初步规格，或填写修改后的规格" class="min-w-0 flex-1 rounded border-blue-200 text-sm"><button class="shrink-0 rounded bg-blue-700 px-3 py-2 font-semibold text-white">确认并反馈产品部</button></form></article>@endforeach</div></section>@endif
            <section class="mt-5 rounded-xl border border-violet-200 bg-violet-50 p-5"><h3 class="font-semibold text-violet-900">流量部投放信息与反馈</h3><p class="mt-1 text-sm text-violet-800">所有部门均可查看；每次广告数据调整都会按时间线保留。</p><div class="mt-3 space-y-3">@forelse($selectedProject->campaignTests as $campaign)<div class="rounded-lg bg-white p-4 text-sm"><p class="font-semibold">{{ strtoupper($campaign->platform) }} · {{ $campaign->campaign_name }}</p><p class="mt-1 text-slate-600">花费 ${{ number_format((float) $campaign->spend, 2) }} · 单点 ${{ number_format((float) $campaign->cost_per_click, 2) }} · 加车 {{ $campaign->add_to_cart_conversions ?? 0 }} · 结账 {{ $campaign->checkout_conversions ?? $campaign->conversions }}</p>@if($canEdit && $stage === 'traffic_growth')<details class="mt-3 rounded border border-violet-200 p-3"><summary class="cursor-pointer font-medium text-violet-900">修改广告数据并新增投放结论</summary><form method="POST" action="{{ route('projects.campaign-tests.update', [$selectedProject, $campaign]) }}" class="mt-3 grid gap-2 md:grid-cols-2">@csrf @method('PATCH')<input name="spend" type="number" step="0.01" min="0" value="{{ $campaign->spend }}" required class="rounded border-slate-300 text-sm"><input name="cost_per_click" type="number" step="0.01" min="0" value="{{ $campaign->cost_per_click }}" required class="rounded border-slate-300 text-sm"><input name="add_to_cart_conversions" type="number" min="0" value="{{ $campaign->add_to_cart_conversions }}" required class="rounded border-slate-300 text-sm"><input name="checkout_conversions" type="number" min="0" value="{{ $campaign->checkout_conversions }}" required class="rounded border-slate-300 text-sm"><textarea name="conclusion" required placeholder="新的投放结论" class="rounded border-slate-300 text-sm md:col-span-2"></textarea><textarea name="adjustment_items" required placeholder="新的调整事项：产品、页面、素材或 SKU 需要如何调整" class="rounded border-slate-300 text-sm md:col-span-2"></textarea><div class="md:col-span-2"><button class="rounded bg-violet-700 px-3 py-2 text-sm font-semibold text-white">保存新版本</button></div></form></details>@endif<div class="mt-3 border-l-2 border-violet-200 pl-3">@forelse($campaign->revisions as $revision)<div class="mb-2"><p class="font-medium">{{ $revision->created_at->format('Y-m-d H:i') }} · 数据调整</p><p class="text-slate-600">结论：{{ $revision->conclusion }}</p><p class="text-slate-600">调整事项：{{ $revision->adjustment_items }}</p></div>@empty<p class="text-slate-500">尚无后续调整记录。</p>@endforelse</div></div>@empty<p class="text-sm text-slate-500">暂未录入投放数据。</p>@endforelse</div><div class="mt-3 space-y-2">@forelse($selectedProject->optimizationFeedback as $feedback)<div class="rounded-lg bg-white p-3 text-sm"><span class="font-semibold">给 {{ $labels[$feedback->target_stage] ?? $feedback->target_stage }}：</span>{{ $feedback->note }}@if($feedback->response_note)<p class="mt-1 text-slate-600">处理说明：{{ $feedback->response_note }}</p>@endif</div>@empty<p class="text-sm text-slate-500">暂未发出优化反馈。</p>@endforelse</div></section>
            @if($stage === 'traffic_growth' && $canEdit)<section class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-5"><h3 class="font-semibold text-rose-900">项目管理</h3><p class="mt-1 text-sm text-rose-800">归档不会删除历史资料；归档后可随时恢复。</p>@if($selectedProject->status === 'archived')<form method="POST" action="{{ route('projects.restore', $selectedProject) }}" class="mt-3">@csrf @method('PATCH')<button class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">恢复产品项目</button></form>@else<form method="POST" action="{{ route('projects.archive', $selectedProject) }}" class="mt-3">@csrf @method('PATCH')<button class="rounded bg-rose-600 px-4 py-2 text-sm font-semibold text-white">归档（删除）产品项目</button></form>@endif</section>@endif
            @if($stage === 'market_research' && $canEdit)<section class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4"><div class="flex items-baseline justify-between gap-3"><div><h3 class="font-semibold text-amber-950">向运营部确认详情页 SKU</h3><p class="mt-1 text-sm text-amber-800">运营部的回答会保存在对应需求下，供产品部决定需要开通哪些内部 SKU。</p></div></div><form method="POST" action="{{ route('projects.decisions.store', $selectedProject) }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_2fr_auto]">@csrf<input type="hidden" name="decision_type" value="sku"><input type="hidden" name="requested_from_stage" value="website_operations"><input name="title" required placeholder="询问详情页需要哪些 SKU" class="rounded border-amber-200 text-sm"><input name="details" required placeholder="说明准备开通的 SKU 和需确认的问题" class="rounded border-amber-200 text-sm"><button class="rounded bg-amber-600 px-3 py-2 text-sm font-semibold text-white">发送给运营部</button></form><div class="mt-4 space-y-3">@forelse($selectedProject->decisions->where('requested_from_stage', 'website_operations') as $decision)<article class="rounded-lg border border-amber-200 bg-white p-3 text-sm"><p class="font-medium">{{ $decision->title }}</p><p class="mt-1 text-slate-600">{{ data_get($decision->details, 'note') }}</p>@if($decision->response_note)<div class="mt-3 rounded bg-emerald-50 p-3"><p class="font-semibold text-emerald-900">运营部回复</p><p class="mt-1 text-emerald-800">{{ $decision->response_note }}</p></div>@else<p class="mt-3 text-amber-800">等待运营部回复</p>@endif</article>@empty<p class="text-sm text-amber-800">尚未向运营部发出 SKU 确认需求。</p>@endforelse</div></section>@endif
        </section>
    @endif
    @if($departmentWorkspace && $selectedProject)
        <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5">
            <h3 class="font-semibold">本部门待处理投放反馈</h3>
            <div class="mt-3 space-y-3">
                @forelse($selectedProject->optimizationFeedback->where('target_stage', $feedbackTargetStage)->where('status', '!=', 'resolved') as $feedback)
                    <div class="rounded-lg bg-slate-50 p-3 text-sm"><p>{{ $feedback->note }}</p>
                        <form method="POST" action="{{ route('projects.optimization-feedback.update', [$selectedProject, $feedback]) }}" class="mt-3 grid gap-2 md:grid-cols-[140px_1fr_auto]">@csrf @method('PATCH')
                            <select name="status" class="rounded border-slate-300 text-sm"><option value="in_progress">处理中</option><option value="resolved">已完成</option></select>
                            <input name="response_note" required placeholder="填写处理结果" class="rounded border-slate-300 text-sm">
                            <button class="rounded bg-slate-800 px-3 py-2 text-sm font-semibold text-white">提交处理</button>
                        </form>
                    </div>
                @empty<p class="text-sm text-slate-500">暂无需要本部门处理的投放反馈。</p>@endforelse
            </div>
        </section>
    @endif
    @if($departmentWorkspace && $selectedProject)
        <section class="mt-5 grid gap-4 lg:grid-cols-2">
            <article class="rounded-xl border border-yellow-300 bg-yellow-50 p-5">
                <h3 class="font-semibold text-yellow-950">可下载的创意素材</h3>
                <p class="mt-1 text-sm text-yellow-900">所有部门均可直接下载创意部已上传的文件；外链素材保留原始链接。</p>
                <div class="mt-3 space-y-2">
                    @forelse($selectedProject->creativeAssets as $asset)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-white p-3 text-sm"><span class="min-w-0 truncate font-medium">{{ $asset->title }}</span>@if($asset->storage_path)<a href="{{ route('projects.creative-assets.download', [$selectedProject, $asset]) }}" class="shrink-0 rounded bg-yellow-500 px-3 py-2 font-semibold text-slate-950">下载</a>@elseif($asset->external_url)<a href="{{ $asset->external_url }}" target="_blank" rel="noreferrer" class="shrink-0 rounded border border-yellow-500 px-3 py-2 font-semibold text-yellow-950">打开链接</a>@else<span class="text-slate-500">仅文案</span>@endif</div>
                    @empty <p class="text-sm text-yellow-900">暂未上传可下载素材。</p>
                    @endforelse
                </div>
            </article>
            <article class="rounded-xl border border-violet-300 bg-violet-50 p-5">
                <h3 class="font-semibold text-violet-950">Facebook 投放关键指标</h3>
                <p class="mt-1 text-sm text-violet-900">基于花费、展示、点击、加车、结账、购买数和购买金额自动换算。</p>
                <div class="mt-3 space-y-2">
                    @forelse($selectedProject->campaignTests as $campaign)
                        @php
                            $clicks = (int) $campaign->clicks;
                            $atc = (int) ($campaign->add_to_cart_conversions ?? 0);
                            $checkout = (int) ($campaign->checkout_conversions ?? 0);
                            $purchases = (int) ($campaign->purchase_conversions ?? $checkout);
                            $resultMetric = $campaign->result_metric ?? 'purchase';
                            $resultLabel = ['purchase' => '购买', 'checkout' => '结账', 'add_to_cart' => '加车', 'click' => '链接点击'][$resultMetric] ?? '购买';
                            $resultCount = ['purchase' => $purchases, 'checkout' => $checkout, 'add_to_cart' => $atc, 'click' => $clicks][$resultMetric] ?? $purchases;
                        @endphp
                        <div class="rounded-lg bg-white p-3 text-sm"><p class="font-medium">{{ $campaign->campaign_name }}</p><p class="mt-1 text-slate-700">加车率 {{ $clicks ? number_format($atc / $clicks * 100, 2) : '—' }}% · 结账率 {{ $atc ? number_format($checkout / $atc * 100, 2) : '—' }}% · 购买转化率 {{ $clicks ? number_format($purchases / $clicks * 100, 2) : '—' }}%</p><p class="mt-1 text-slate-700">CPA ${{ $purchases ? number_format((float) $campaign->spend / $purchases, 2) : '—' }} · ROAS {{ $campaign->spend > 0 ? number_format((float) ($campaign->purchase_value ?? 0) / $campaign->spend, 2) : '—' }} · 购买金额 ${{ number_format((float) ($campaign->purchase_value ?? 0), 2) }}</p><p class="mt-1 font-medium text-violet-900">CPR（{{ $resultLabel }}） ${{ $resultCount ? number_format((float) $campaign->spend / $resultCount, 2) : '—' }}</p>@if($stage === 'traffic_growth' && $canEdit)<details class="mt-3 border-t border-violet-100 pt-3"><summary class="cursor-pointer font-medium text-violet-900">更新本次投放数据</summary><form method="POST" action="{{ route('projects.campaign-tests.update', [$selectedProject, $campaign]) }}" class="mt-3 grid gap-2 md:grid-cols-2">@csrf @method('PATCH')<input name="spend" required type="number" min="0" step="0.01" value="{{ $campaign->spend }}" class="field-input"><input name="cost_per_click" required type="number" min="0" step="0.01" value="{{ $campaign->cost_per_click }}" class="field-input"><input name="add_to_cart_conversions" required type="number" min="0" value="{{ $campaign->add_to_cart_conversions }}" class="field-input"><input name="checkout_conversions" required type="number" min="0" value="{{ $campaign->checkout_conversions }}" class="field-input"><input name="purchase_conversions" required type="number" min="0" value="{{ $campaign->purchase_conversions ?? $campaign->checkout_conversions }}" class="field-input"><input name="purchase_value" required type="number" min="0" step="0.01" value="{{ $campaign->purchase_value ?? 0 }}" class="field-input"><textarea name="conclusion" required placeholder="新的投放结论" class="field-input md:col-span-2"></textarea><textarea name="adjustment_items" required placeholder="需要调整的产品、页面、素材或规格" class="field-input md:col-span-2"></textarea><button class="rounded bg-violet-700 px-3 py-2 text-sm font-semibold text-white md:col-span-2">保存新版本</button></form></details>@endif</div>
                    @empty <p class="text-sm text-violet-900">暂无可计算的投放记录。</p>
                    @endforelse
                </div>
            </article>
        </section>
    @endif
    @if($departmentWorkspace && $selectedProject)
        @php $outcomeLabels = ['scale' => '继续放量', 'retest' => '继续测试', 'adjust_retest' => '调整后复测', 'pause' => '暂停', 'reject' => '淘汰', 'complete' => '已完成']; @endphp
        <section class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-5"><h3 class="font-semibold text-emerald-900">项目最终结论</h3>
            @if($selectedProject->outcome)<p class="mt-2 font-medium">{{ $outcomeLabels[$selectedProject->outcome] }}</p><p class="mt-1 text-sm">结论依据：{{ $selectedProject->outcome_reason }}</p><p class="mt-1 text-sm">下一步：{{ $selectedProject->next_action }}</p>@else<p class="mt-2 text-sm text-emerald-800">尚未形成最终结论。</p>@endif
            @if($stage === 'traffic_growth' && $canEdit)<form method="POST" action="{{ route('projects.outcome', $selectedProject) }}" class="mt-4 grid gap-2 md:grid-cols-2">@csrf @method('PATCH')<select name="outcome" required class="rounded border-emerald-200 text-sm"><option value="">选择最终结论</option>@foreach($outcomeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><input name="outcome_reason" required placeholder="结论依据：关键数据与判断" class="rounded border-emerald-200 text-sm"><textarea name="next_action" required placeholder="下一步安排" class="rounded border-emerald-200 text-sm md:col-span-2"></textarea><div><button class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">保存项目结论</button></div></form>@endif
        </section>
    @endif
    @if($departmentWorkspace && $selectedProject)
        <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5">
            <h3 class="font-semibold">关联协作摘要</h3>
            <dl class="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg bg-slate-50 p-3"><dt>内部 SKU</dt><dd class="mt-1 text-lg font-semibold text-slate-900">{{ $selectedProject->skus->count() }} 个</dd></div>
                <div class="rounded-lg bg-slate-50 p-3"><dt>详情页</dt><dd class="mt-1 text-lg font-semibold text-slate-900">{{ $selectedProject->landingPages->count() }} 页</dd></div>
                <div class="rounded-lg bg-slate-50 p-3"><dt>素材</dt><dd class="mt-1 text-lg font-semibold text-slate-900">{{ $selectedProject->creativeAssets->count() }} 个</dd></div>
                <div class="rounded-lg bg-slate-50 p-3"><dt>投放记录</dt><dd class="mt-1 text-lg font-semibold text-slate-900">{{ $selectedProject->campaignTests->count() }} 条</dd></div>
            </dl>
        </section>
    @endif
</x-layouts.app>
<script>
const createProjectForm = document.querySelector('#create-project form');
const currentProductName = @json(optional($selectedProject)->product_name);
const hasPendingInternalSku = @json(optional($selectedProject)->skus?->whereNull('sku_code')->isNotEmpty() ?? false);
const pendingInternalSkus = @json(optional($selectedProject)->skus?->whereNull('sku_code')->map(fn ($sku) => ['id' => $sku->id, 'name' => $sku->variant_name])->values() ?? []);
const internalSkuEndpoint = @json($selectedProject ? route('projects.skus.store', $selectedProject) : null);
document.querySelectorAll('form[action*="/sources"] input[name="sku_code"]').forEach((field) => {
    field.disabled = true;
    field.required = false;
    field.closest('label')?.classList.add('hidden');
    field.form?.querySelector('button')?.replaceChildren('保存货源与产品规格');
});
const specificationList = document.querySelector('[data-specification-list]');
const addSpecificationButton = document.querySelector('[data-add-specification]');
const addSpecification = () => {
    if (!specificationList) return;
    const index = specificationList.children.length;
    const row = document.createElement('div');
    row.className = 'grid gap-3 rounded-xl border border-orange-200 bg-orange-50 p-3 md:grid-cols-2 xl:grid-cols-4';
    row.innerHTML = `<label class="field-label">产品规格 <span>*</span><input name="specifications[${index}][variant_name]" required placeholder="例如：单件 / 两件套 / 尺寸版本" class="field-input"></label><label class="field-label">公司内部 SKU <span>*</span><input name="specifications[${index}][sku_code]" required placeholder="该规格对应的内部 SKU" class="field-input"></label><label class="field-label">采购价（CNY）<span>*</span><input name="specifications[${index}][purchase_price]" required type="number" step="0.01" min="0" placeholder="例如：19.90" class="field-input"></label><label class="field-label">重量（g）<span>*</span><input name="specifications[${index}][weight_g]" required type="number" min="0" placeholder="例如：350" class="field-input"></label><button type="button" class="justify-self-start text-sm font-semibold text-red-700 xl:col-span-4" data-remove-specification>删除此规格</button>`;
    row.querySelector('[data-remove-specification]')?.addEventListener('click', () => { if (specificationList.children.length > 1) row.remove(); });
    specificationList.append(row);
};
addSpecificationButton?.addEventListener('click', addSpecification);
if (specificationList?.children.length === 0) addSpecification();
const alternativeSourceList = document.querySelector('[data-alternative-source-list]');
const addAlternativeSourceButton = document.querySelector('[data-add-alternative-source]');
const addAlternativeSource = () => {
    if (!alternativeSourceList) return;
    const index = alternativeSourceList.children.length;
    const row = document.createElement('div');
    row.className = 'grid gap-3 rounded-xl border border-orange-200 bg-orange-50 p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)_auto]';
    row.innerHTML = `<label class="field-label">其他供应商名称 <span>*</span><input name="alternative_sources[${index}][supplier_name]" required placeholder="例如：备选工厂 A" class="field-input"></label><label class="field-label">其他 1688 货源地址 <span>*</span><input name="alternative_sources[${index}][supplier_url]" type="url" required placeholder="粘贴备选商家 1688 链接" class="field-input"></label><button type="button" data-remove-alternative-source class="self-end rounded border border-orange-300 px-3 py-2 text-sm font-semibold text-orange-900">删除</button>`;
    row.querySelector('[data-remove-alternative-source]')?.addEventListener('click', () => row.remove());
    alternativeSourceList.append(row);
};
addAlternativeSourceButton?.addEventListener('click', addAlternativeSource);
if (hasPendingInternalSku && internalSkuEndpoint) {
    const flow = [...document.querySelectorAll('section')].find((section) => section.textContent.includes('产品部执行顺序'));
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = internalSkuEndpoint;
    form.className = 'mt-4 grid gap-3 border-t border-orange-200 pt-4 md:grid-cols-[1fr_1fr_auto]';
    const token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}'; form.append(token);
    const choice = document.createElement('label'); choice.className = 'field-label'; choice.append('待回填产品规格 *');
    const select = document.createElement('select'); select.name = 'product_sku_id'; select.required = true; select.className = 'field-input';
    pendingInternalSkus.forEach((sku) => { const option = document.createElement('option'); option.value = sku.id; option.textContent = sku.name; select.append(option); });
    choice.append(select); form.append(choice);
    const code = document.createElement('label'); code.className = 'field-label'; code.append('公司内部 SKU *');
    const input = document.createElement('input'); input.name = 'sku_code'; input.required = true; input.placeholder = '从公司内部系统生成后填入'; input.className = 'field-input'; code.append(input); form.append(code);
    const action = document.createElement('div'); action.className = 'self-end'; const button = document.createElement('button'); button.className = 'rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white'; button.textContent = '回填内部 SKU'; action.append(button); form.append(action);
    flow?.append(form);
}
document.querySelectorAll('form[action$="/decisions"] input[name="decision_type"][value="specification"]').forEach((field) => {
    const form = field.closest('form');
    if (hasPendingInternalSku) form?.classList.add('hidden');
    const title = form?.querySelector('input[name="title"]');
    if (title?.value === '确认路西法产品规格' && currentProductName) title.value = `确认 ${currentProductName} 产品规格`;
});
const createProjectError = document.createElement('p');
createProjectError.className = 'mt-3 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-800';
createProjectForm?.append(createProjectError);
createProjectForm?.addEventListener('invalid', (event) => {
    const field = event.target;
    const messages = {
        product_name: '请填写产品名称。',
        category: '请选择产品类目。',
        priority: '请先选择产品阶段，再创建产品项目。',
        product_image: '请上传一张 PNG、JPG 或 WebP 格式的产品主图。',
    };
    const message = messages[field.name];
    if (!message) return;
    field.setCustomValidity(message);
    createProjectError.textContent = message;
    createProjectError.classList.remove('hidden');
}, true);
createProjectForm?.querySelectorAll('input, select').forEach((field) => {
    field.addEventListener('input', () => field.setCustomValidity(''));
    field.addEventListener('change', () => field.setCustomValidity(''));
});
document.querySelectorAll('form[action*="/campaign-tests"]:not([action$="update"])').forEach((form) => {
    const checkout = form.querySelector('input[name="checkout_conversions"]');
    if (!checkout || form.querySelector('input[name="purchase_conversions"]')) return;
    checkout.insertAdjacentHTML('afterend', `<label class="field-label">购买数 <span>*</span><input name="purchase_conversions" type="number" min="0" required placeholder="用于自动计算 CPA / 转化率" class="field-input"></label><label class="field-label">购买金额（USD） <span>*</span><input name="purchase_value" type="number" step="0.01" min="0" required placeholder="用于自动计算 ROAS" class="field-input"></label>`);
});
document.querySelectorAll('form[action$="/decisions"] input[name="decision_type"]').forEach((input) => {
    input.value = 'specification';
    const section = input.closest('section');
    section?.querySelector('h3')?.replaceChildren('向运营部确认最终产品规格');
    const description = section?.querySelector('p');
    if (description) description.textContent = '产品部提交初步产品规格；运营部确认采用或修改后，产品部才能开发公司内部 SKU。';
});
document.querySelectorAll('[data-paste-upload]').forEach((zone) => {
    zone.addEventListener('paste', (event) => {
        const image = Array.from(event.clipboardData?.files || []).find((file) => file.type.startsWith('image/'));
        if (!image) return;
        event.preventDefault();
        const input = zone.querySelector('input[name="detail_image"]');
        const files = new DataTransfer();
        files.items.add(image);
        input.files = files.files;
        zone.querySelector('[data-paste-upload-status]').textContent = `已粘贴截图：${image.name || '截图.png'}`;
    });
});

document.querySelectorAll('form:has(input[name="search"]), form:has(select[name="project"])').forEach((form) => {
    form.addEventListener('submit', () => {
        form.action = `${form.action.split('#')[0]}#project-work-area`;
    });
});

const mainImageFigure = document.querySelector('.dept-panel-market_research figure');
const productImageUpdateAction = mainImageFigure?.dataset.imageUpdateAction;
if (mainImageFigure && productImageUpdateAction && @json($stage === 'market_research' && $canEdit) && !mainImageFigure.querySelector('[data-product-image-update]')) {
    const imageUpdateForm = document.createElement('form');
    imageUpdateForm.dataset.productImageUpdate = 'true';
    imageUpdateForm.method = 'POST';
    imageUpdateForm.enctype = 'multipart/form-data';
    imageUpdateForm.action = productImageUpdateAction;
    imageUpdateForm.className = 'mt-3 border-t border-orange-100 pt-3';
    imageUpdateForm.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH"><label class="field-label">更换产品主图 <span>*</span><input name="product_image" required type="file" accept="image/png,image/jpeg,image/webp" class="field-input"><small>选择 PNG、JPG 或 WebP；更新后会同步替换所有部门展示的主图。</small></label><button class="mt-2 rounded bg-orange-700 px-3 py-2 text-sm font-semibold text-white">更新产品主图</button>`;
    mainImageFigure.append(imageUpdateForm);
}

const shouldShowSpecificationConfirmation = @json($stage === 'website_operations');
const pendingSpecificationDecisions = @json(isset($incoming) ? $incoming->where('decision_type', 'specification')->values() : []);
if (false) {
pendingSpecificationDecisions.forEach((decision) => {
    if (!shouldShowSpecificationConfirmation) return;
    const form = document.querySelector(`form[action$="/decisions/${decision.id}"]`);
    if (!form || form.querySelector('[data-initial-specifications]')) return;
    const initialSpecifications = decision.details?.initial_specifications || [];
    if (!initialSpecifications.length) return;
    const summary = document.createElement('div');
    summary.dataset.initialSpecifications = 'true';
    summary.className = 'mt-3 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-slate-700';
    const heading = document.createElement('p');
    heading.className = 'font-semibold text-blue-950';
    heading.textContent = '产品部初步规格（只读）';
    const list = document.createElement('ul');
    list.className = 'mt-2 space-y-1';
    initialSpecifications.forEach((specification) => {
        const item = document.createElement('li');
        item.textContent = `${specification.sku_code} · ${specification.variant_name} · 采购价 ¥${specification.purchase_price ?? '待补'} · 重量 ${specification.weight_g ?? '待补'}g`;
        list.append(item);
    });
    summary.append(heading, list);
    form.before(summary);
    if (form.dataset.specificationRequestForm) return;
    form.dataset.specificationRequestForm = 'true';
    form.className = 'mt-3 grid gap-3 rounded-lg border border-blue-200 bg-white p-3';
    form.querySelector('input[name="response_note"]')?.remove();
    const instruction = document.createElement('p');
    instruction.className = 'text-sm text-slate-600';
    instruction.textContent = '该规格是否满足运营需求？';
    form.prepend(instruction);
    const requestList = document.createElement('div');
    requestList.dataset.requestedSpecifications = 'true';
    requestList.className = 'hidden space-y-2';
    const addRequest = () => {
        const row = document.createElement('div');
        row.className = 'flex gap-2';
        const input = document.createElement('input');
        input.name = 'requested_specifications[]';
        input.required = true;
        input.placeholder = '例如：两件套 / 礼盒装 / 新尺寸版本';
        input.className = 'field-input min-w-0 flex-1';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'rounded border border-blue-200 px-3 text-sm text-blue-800';
        remove.textContent = '删除';
        remove.addEventListener('click', () => row.remove());
        row.append(input, remove);
        requestList.append(row);
    };
    const requestHeading = document.createElement('div');
    requestHeading.className = 'hidden items-center justify-between gap-3';
    const requestLabel = document.createElement('p');
    requestLabel.className = 'font-semibold text-blue-950';
    requestLabel.textContent = '新增产品规格需求';
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'rounded border border-blue-300 px-3 py-2 text-sm font-semibold text-blue-900';
    addButton.textContent = '＋ 添加产品规格';
    addButton.addEventListener('click', addRequest);
    requestHeading.append(requestLabel, addButton);
    form.append(requestHeading, requestList);
    addRequest();
    const submit = form.querySelector('button');
    submit?.classList.remove('shrink-0');
    if (submit) {
        submit.name = 'specification_action';
        submit.value = 'request';
        submit.classList.add('hidden');
        submit.textContent = '发送新增规格给产品部生成内部 SKU';
        form.append(submit);
    }
    const adopt = document.createElement('button');
    adopt.type = 'submit';
    adopt.name = 'specification_action';
    adopt.value = 'adopt';
    adopt.formNoValidate = true;
    adopt.className = 'rounded bg-blue-700 px-4 py-2 text-sm font-semibold text-white';
    adopt.textContent = '确认采用并反馈产品部';
    const actions = document.createElement('div');
    actions.className = 'flex flex-wrap gap-2';
    const request = document.createElement('button');
    request.type = 'button';
    request.className = 'rounded border border-blue-300 bg-white px-4 py-2 text-sm font-semibold text-blue-800';
    request.textContent = '需要新增规格';
    request.addEventListener('click', () => {
        requestHeading.classList.remove('hidden');
        requestHeading.classList.add('flex');
        requestList.classList.remove('hidden');
        submit?.classList.remove('hidden');
        request.classList.add('hidden');
        requestList.querySelector('input')?.focus();
    });
    actions.append(adopt, request);
    submit?.insertAdjacentElement('beforebegin', actions);
});

const operationsHeading = Array.from(document.querySelectorAll('h3')).find((heading) => heading.textContent.trim() === '最终产品规格与 Shopify 产品');
const operationsPanel = operationsHeading?.closest('.dept-panel-website_operations');
const specificationConfirmation = Array.from(document.querySelectorAll('section')).find((section) => section.querySelector('h3')?.textContent.trim() === '待确认的初步产品规格');
if (operationsPanel && specificationConfirmation && shouldShowSpecificationConfirmation) {
    const shopifyForm = operationsPanel.querySelector('form[action*="/landing-pages"]');
    operationsPanel.insertBefore(specificationConfirmation, shopifyForm?.parentElement || null);
    specificationConfirmation.classList.remove('mt-5');
    specificationConfirmation.classList.add('mt-4');
    specificationConfirmation.querySelector('h3').textContent = '产品规格确认';
    specificationConfirmation.querySelector('h3 + p').textContent = '查看产品部提交的规格，确认采用或按需新增。';
    const workflowDescription = operationsHeading?.nextElementSibling;
    if (workflowDescription?.tagName === 'P') workflowDescription.textContent = '查看产品部规格，确认采用或提出新增规格需求；新增规格由产品部生成对应的内部 SKU。';
}
}

document.querySelectorAll('[data-open-specification-request]').forEach((button) => {
    button.addEventListener('click', () => {
        const panel = button.closest('section');
        const form = panel?.querySelector('[data-specification-request-form]');
        if (!form) return;
        form.hidden = false;
        form.querySelector('input[name="requested_specifications[]"]')?.focus();
    });
});

document.querySelectorAll('[data-add-specification-request]').forEach((button) => {
    button.addEventListener('click', () => {
        const list = button.closest('form')?.querySelector('[data-specification-request-list]');
        if (!list) return;
        const row = document.createElement('div');
        row.className = 'flex gap-2';
        row.innerHTML = '<input name="requested_specifications[]" required placeholder="例如：两件套 / 礼盒装 / 新尺寸版本" class="field-input min-w-0 flex-1"><button type="button" data-remove-specification-request class="rounded border border-blue-200 px-3 text-sm text-blue-800">删除</button>';
        row.querySelector('[data-remove-specification-request]')?.addEventListener('click', () => row.remove());
        list.append(row);
    });
});

document.querySelectorAll('[data-remove-specification-request]').forEach((button) => {
    button.addEventListener('click', () => button.closest('.flex')?.remove());
});

document.querySelectorAll('[data-add-reference-url]').forEach((button) => {
    button.addEventListener('click', () => {
        const list = button.closest('div.rounded-lg')?.querySelector('[data-reference-url-list]');
        if (!list) return;
        const row = document.createElement('div');
        row.className = 'flex gap-2';
        row.innerHTML = '<input name="reference_urls[]" type="url" required placeholder="粘贴参考素材链接" class="field-input mt-0 min-w-0 flex-1"><button type="button" data-remove-reference-url class="rounded border border-yellow-300 px-3 text-sm font-semibold text-yellow-900">删除</button>';
        row.querySelector('[data-remove-reference-url]')?.addEventListener('click', () => row.remove());
        list.append(row);
    });
});
document.querySelectorAll('[data-remove-reference-url]').forEach((button) => {
    button.addEventListener('click', () => {
        const list = button.closest('[data-reference-url-list]');
        if (list?.children.length > 1) button.closest('.flex')?.remove();
    });
});

Array.from(document.querySelectorAll('section')).find((section) => section.querySelector('h3')?.textContent.trim() === '待确认的初步产品规格')?.remove();

@if($selectedProject)
const sharedMaterialsPanel = Array.from(document.querySelectorAll('section')).find((section) => section.querySelector('h3')?.textContent.trim() === '项目共享资料');
const sharedMaterialsGrid = sharedMaterialsPanel?.querySelector(':scope > .mt-4.grid');
const standaloneSharedMaterials = Array.from(document.querySelectorAll('section')).find((section) => {
    const headings = Array.from(section.querySelectorAll(':scope > article > h3')).map((heading) => heading.textContent.trim());
    return headings.includes('可下载的创意素材') && headings.includes('Facebook 投放关键指标');
});
const pendingFeedbackPanel = Array.from(document.querySelectorAll('section')).find((section) => section.querySelector('h3')?.textContent.trim() === '本部门待处理投放反馈');
const sharedCampaignFeedbackPanel = Array.from(document.querySelectorAll('section')).find((section) => section.querySelector('h3')?.textContent.trim() === '共享投放数据与协作反馈');
if (sharedMaterialsGrid) {
    sharedMaterialsGrid.classList.remove('xl:grid-cols-3');
    sharedMaterialsGrid.classList.add('xl:grid-cols-4');
    const creativeSharedMaterials = Array.from(sharedMaterialsGrid.children).find((card) => card.querySelector(':scope > p')?.textContent.trim() === '创意部资料');
    const creativeDownloads = Array.from(standaloneSharedMaterials?.querySelectorAll(':scope > article') || []).find((card) => card.querySelector('h3')?.textContent.trim() === '可下载的创意素材');
    const facebookMetrics = Array.from(standaloneSharedMaterials?.querySelectorAll(':scope > article') || []).find((card) => card.querySelector('h3')?.textContent.trim() === 'Facebook 投放关键指标');
    if (creativeSharedMaterials && creativeDownloads) {
        const creativeDownloadActions = Array.from(creativeDownloads.querySelectorAll('a')).map((action) => ({
            title: action.closest('div')?.querySelector('span')?.textContent.trim(),
            action,
        }));
        creativeDownloadActions.forEach(({title, action}) => {
            const assetCard = Array.from(creativeSharedMaterials.querySelectorAll('.rounded.bg-white.p-3')).find((card) => card.querySelector('.font-medium')?.textContent.trim() === title);
            if (!assetCard || assetCard.querySelector('a')) return;
            const downloadAction = action.cloneNode(true);
            downloadAction.className = 'mt-2 inline-flex rounded bg-yellow-500 px-3 py-2 text-sm font-semibold text-slate-950';
            assetCard.append(downloadAction);
        });
        creativeDownloads.remove();
    }
    const trafficSharedMaterials = document.createElement('article');
    trafficSharedMaterials.className = 'rounded-lg bg-slate-50 p-4 text-sm';
    const adStatusLabels = {not_started: '未开启', active: '投放中', paused: '已暂停', ended: '已结束'};
    const adDeliveryStatus = @json($selectedProject->ad_delivery_status ?? 'not_started');
    const adStartedAt = @json($selectedProject->ad_started_at?->format('Y-m-d H:i'));
    const adStatusUpdatedAt = @json($selectedProject->ad_delivery_status_updated_at?->format('Y-m-d H:i'));
    trafficSharedMaterials.innerHTML = `<p class="font-semibold">流量部资料</p><p class="mt-2 text-slate-600">Facebook 投放关键指标与待处理反馈</p><div class="mt-3 rounded bg-white p-3"><p class="font-medium">项目广告投放状态：${adStatusLabels[adDeliveryStatus] ?? '未开启'}</p><p class="mt-1 text-slate-500">开始投放时间：${adStartedAt ?? '尚未设置'}</p></div>`;
    trafficSharedMaterials.querySelector('.rounded')?.insertAdjacentHTML('beforeend', `<p class="mt-1 text-xs text-slate-400">最后更新于 ${adStatusUpdatedAt ?? '尚未更新'}</p>`);
    if (facebookMetrics) {
        facebookMetrics.className = 'mt-4 border-t border-slate-200 pt-4';
        facebookMetrics.querySelector('h3')?.classList.remove('text-violet-950');
        facebookMetrics.querySelector('h3')?.classList.add('text-slate-900');
        trafficSharedMaterials.append(facebookMetrics);
    }
    const hasPendingFeedback = pendingFeedbackPanel && !pendingFeedbackPanel.textContent.includes('暂无需要本部门处理的投放反馈。');
    if (hasPendingFeedback) {
        pendingFeedbackPanel.className = 'mt-4 border-t border-slate-200 pt-4';
        trafficSharedMaterials.append(pendingFeedbackPanel);
    } else {
        pendingFeedbackPanel?.remove();
    }
    sharedCampaignFeedbackPanel?.remove();
    sharedMaterialsGrid.append(trafficSharedMaterials);
    standaloneSharedMaterials?.remove();
    const sharedDepartmentCardPalette = {
        '产品部资料': 'shared-department-card-product',
        '运营部资料': 'shared-department-card-operations',
        '创意部资料': 'shared-department-card-creative',
        '流量部资料': 'shared-department-card-traffic',
    };
    Array.from(sharedMaterialsGrid.children).forEach((card) => {
        const departmentTitle = card.querySelector(':scope > p')?.textContent.trim();
        const paletteClass = sharedDepartmentCardPalette[departmentTitle];
        if (paletteClass) card.classList.add('shared-department-card', paletteClass);
    });
}

const submittedRecords = {
    research: @json($selectedProject->researchSources->map(fn ($record) => $record->created_at?->format('Y-m-d H:i'))->values()),
    skus: @json($selectedProject->skus->map(fn ($record) => $record->created_at?->format('Y-m-d H:i'))->values()),
    sources: @json($selectedProject->sources->map(fn ($record) => $record->created_at?->format('Y-m-d H:i'))->values()),
    landingPages: @json($selectedProject->landingPages->map(fn ($record) => $record->created_at?->format('Y-m-d H:i'))->values()),
    creativeAssets: @json($selectedProject->creativeAssets->map(fn ($record) => $record->created_at?->format('Y-m-d H:i'))->values()),
    campaigns: @json($selectedProject->campaignTests->map(fn ($record) => ['title' => $record->campaign_name, 'submittedAt' => $record->created_at?->format('Y-m-d H:i')])->values()),
    feedback: @json($selectedProject->optimizationFeedback->map(fn ($record) => ['note' => $record->note, 'submittedAt' => $record->created_at?->format('Y-m-d H:i')])->values()),
};
const appendSubmittedAt = (element, submittedAt) => {
    if (!element || !submittedAt || element.querySelector('[data-submitted-at]') || element.textContent.includes('提交于')) return;
    const timestamp = document.createElement('p');
    timestamp.dataset.submittedAt = 'true';
    timestamp.className = 'mt-1 text-xs text-slate-400';
    timestamp.textContent = `提交于 ${submittedAt}`;
    element.append(timestamp);
};
const appendSubmittedAtByIndex = (elements, submittedAtValues) => {
    Array.from(elements).forEach((element, index) => appendSubmittedAt(element, submittedAtValues[index]));
};

const selectedEvidenceSubmittedAt = @json($selectedProject->researchSources->first()?->created_at?->format('Y-m-d H:i'));
const selectedEvidenceHeading = Array.from(document.querySelectorAll('p')).find((heading) => heading.textContent.trim() === '当前选品信息');
appendSubmittedAt(selectedEvidenceHeading?.parentElement, selectedEvidenceSubmittedAt);
const campaignFeedbackForm = Array.from(document.querySelectorAll('form')).find((form) => form.querySelector('textarea[name="feedback_note"]'));
const latestCampaignFeedbackUpdatedAt = @json($selectedProject->campaignTests->sortByDesc('updated_at')->first()?->updated_at?->format('Y-m-d H:i'));
if (campaignFeedbackForm && !campaignFeedbackForm.querySelector('[data-daily-campaign-update]')) {
    const updateSchedule = document.createElement('div');
    updateSchedule.dataset.dailyCampaignUpdate = 'true';
    updateSchedule.className = 'rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-sm text-violet-900 md:col-span-2';
    updateSchedule.innerHTML = `<span class="font-semibold">广告数据反馈：每日 16:00 更新</span><span class="ml-2 text-violet-700">最近一次提交：${latestCampaignFeedbackUpdatedAt ?? '尚未提交'}</span>`;
    campaignFeedbackForm.prepend(updateSchedule);
}
const campaignRawMetrics = @json($selectedProject->campaignTests->mapWithKeys(fn ($campaign) => [$campaign->id => ['impressions' => $campaign->impressions, 'clicks' => $campaign->clicks]]));
document.querySelectorAll('form[action*="/campaign-tests/"]').forEach((form) => {
    if (!form.querySelector('textarea[name="conclusion"]') || form.querySelector('input[name="impressions"]')) return;
    const campaignId = form.action.split('/').pop();
    const metrics = campaignRawMetrics[campaignId] ?? {impressions: 0, clicks: 0};
    const rawMetrics = document.createElement('div');
    rawMetrics.className = 'grid gap-2 md:col-span-2 md:grid-cols-2';
    rawMetrics.innerHTML = `<label class="field-label">展示次数 <span>*</span><input name="impressions" required type="number" min="0" value="${metrics.impressions}" class="field-input"></label><label class="field-label">链接点击 <span>*</span><input name="clicks" required type="number" min="0" value="${metrics.clicks}" class="field-input"></label>`;
    form.prepend(rawMetrics);
});
const adDeliveryStatusPanel = Array.from(document.querySelectorAll('section')).find((section) => section.querySelector('p')?.textContent.trim() === '项目广告投放状态');
if (adDeliveryStatusPanel) {
    const statusTimestamp = document.createElement('p');
    statusTimestamp.className = 'mt-1 text-xs text-violet-800';
    statusTimestamp.textContent = `最后更新于 ${@json($selectedProject->ad_delivery_status_updated_at?->format('Y-m-d H:i')) ?? '尚未更新'}`;
    adDeliveryStatusPanel.querySelector('form')?.insertAdjacentElement('beforebegin', statusTimestamp) || adDeliveryStatusPanel.append(statusTimestamp);
}

if (sharedMaterialsGrid) {
    const sharedDepartmentCard = (title) => Array.from(sharedMaterialsGrid.children).find((card) => card.querySelector(':scope > p')?.textContent.trim() === title);
    const productMaterials = sharedDepartmentCard('产品部资料');
    const productGroups = Array.from(productMaterials?.children || []).filter((child) => child.tagName === 'DIV');
    appendSubmittedAtByIndex(productGroups[0]?.querySelectorAll(':scope > div') || [], submittedRecords.research);
    appendSubmittedAtByIndex(productGroups[1]?.querySelectorAll(':scope > p') || [], submittedRecords.skus);
    appendSubmittedAtByIndex(productGroups[2]?.querySelectorAll(':scope > div') || [], submittedRecords.sources);
    const operationsMaterials = sharedDepartmentCard('运营部资料');
    appendSubmittedAtByIndex(operationsMaterials?.querySelectorAll('.mt-3.space-y-2 > div') || [], submittedRecords.landingPages);
    const creativeMaterials = sharedDepartmentCard('创意部资料');
    appendSubmittedAtByIndex(creativeMaterials?.querySelectorAll('.mt-3.space-y-2 > div') || [], submittedRecords.creativeAssets);
    submittedRecords.campaigns.forEach(({title, submittedAt}) => {
        Array.from(sharedMaterialsGrid.querySelectorAll('.font-medium')).filter((heading) => heading.textContent.trim() === title).forEach((heading) => appendSubmittedAt(heading.parentElement, submittedAt));
    });
    submittedRecords.feedback.forEach(({note, submittedAt}) => {
        Array.from(sharedMaterialsGrid.querySelectorAll('*')).filter((element) => element.children.length === 0 && element.textContent.includes(note)).forEach((element) => appendSubmittedAt(element.parentElement, submittedAt));
    });
}
@endif

document.querySelectorAll('form[action*="/creative-assets"] input[name="title"], form[action*="/campaign-tests"] input[name="campaign_name"]').forEach((input) => {
    input.value = currentProductName;
    input.readOnly = true;
    input.classList.add('bg-slate-100', 'text-slate-500');
    input.closest('label')?.querySelector('span')?.remove();
});

const categorySelect = document.querySelector('#project-category');
const categoryManager = document.querySelector('#category-manager');
const categoryEndpoints = @json($managedCategories->mapWithKeys(fn ($category) => [$category->name => route('product-categories.destroy', $category)]));
const csrfToken = document.querySelector('input[name="_token"]')?.value;
if (categoryManager && !categoryManager.querySelector('[data-close-category-manager]')) {
    categoryManager.insertAdjacentHTML('afterbegin', '<div class="mb-2 flex justify-end"><button type="button" data-close-category-manager class="rounded border border-orange-300 px-2 py-1 text-xs font-semibold text-orange-900 hover:bg-orange-50">完成并关闭</button></div>');
}
categorySelect?.addEventListener('change', () => {
    if (categorySelect.value !== '__manage__') {
        categoryManager.hidden = true;
        return;
    }
    categoryManager.hidden = false;
    categorySelect.value = '';
    categoryManager.querySelector('input[name="name"]')?.focus();
});
categoryManager?.querySelector('[data-close-category-manager]')?.addEventListener('click', () => {
    categoryManager.hidden = true;
    categorySelect?.focus();
});
categoryManager?.querySelectorAll('button[aria-label^="删除 "]').forEach((button) => {
    button.type = 'button';
    button.addEventListener('click', async () => {
        const name = button.getAttribute('aria-label').replace('删除 ', '');
        const endpoint = categoryEndpoints[name];
        if (!endpoint || !window.confirm(`确认删除产品类目“${name}”吗？已有项目会保留其原标签。`)) return;
        button.disabled = true;
        const response = await fetch(endpoint, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
        });
        if (response.ok || response.redirected) window.location.assign(response.url || window.location.href);
        else { button.disabled = false; window.alert('删除失败，请稍后重试。'); }
    });
});
const addCategoryButton = categoryManager?.querySelector('[data-add-category]');
addCategoryButton?.addEventListener('click', async (event) => {
    event.preventDefault();
    addCategoryButton.type = 'button';
    const input = categoryManager.querySelector('input[name="name"]');
    const name = input?.value.trim();
    if (!name) return input?.focus();
    addCategoryButton.disabled = true;
    const response = await fetch('{{ route('product-categories.store') }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({name}),
    });
    if (response.ok || response.redirected) window.location.assign(response.url || window.location.href);
    else { addCategoryButton.disabled = false; window.alert('添加失败：类目名称可能已存在。'); }
});

const projectStageProgress = document.querySelector('[data-project-stage-progress]');
const projectStageDescription = projectStageProgress?.querySelector('[data-project-stage-description]');
projectStageProgress?.querySelectorAll('[data-project-stage-item]').forEach((item) => {
    item.addEventListener('click', () => {
        projectStageProgress.querySelectorAll('[data-project-stage-item]').forEach((stageItem) => stageItem.setAttribute('aria-pressed', 'false'));
        item.setAttribute('aria-pressed', 'true');
        projectStageDescription.textContent = `查看：${item.dataset.stageLabel} · ${item.dataset.stageDescription}`;
    });
});
</script>
