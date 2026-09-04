<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->product_name }} · 产品项目</title>
</head>
<body>
    <main>
        <p><a href="{{ route('projects.index') }}">返回产品项目池</a></p>
        <h1>{{ $project->product_name }}</h1>
        <p>项目编号：{{ $project->project_code }} · 市场：{{ $project->market }} · 当前环节：{{ $project->current_stage }}</p>

        @if (auth()->user()?->department?->code === 'website_operations')
            <section>
                <h2>新建落地页版本</h2>
                <form method="post" action="{{ route('projects.landing-pages.store', $project) }}">
                    @csrf
                    <p><label>版本名称 <input name="title" required></label></p>
                    <p><label>落地页链接 <input name="page_url" type="url" required></label></p>
                    <p><label>销售价格 <input name="selling_price" type="number" min="0" step="0.01"></label></p>
                    <p><label>币种 <input name="currency" value="USD" maxlength="3" required></label></p>
                    <p><label>产品规格 <textarea name="specifications"></textarea></label></p>
                    <fieldset>
                        <legend>关联 SKU</legend>
                        @forelse ($project->skus as $sku)
                            <label><input type="checkbox" name="sku_ids[]" value="{{ $sku->id }}"> {{ $sku->sku_code }} · {{ $sku->variant_name }}</label><br>
                        @empty
                            <p>请先添加货源与 SKU，才可建立落地页版本。</p>
                        @endforelse
                    </fieldset>
                    <p><button type="submit">保存为草稿</button></p>
                </form>
            </section>
        @endif

        @if (auth()->user()?->department?->code === 'traffic_growth')
            <section>
                <h2>记录投放测试</h2>
                <form method="post" action="{{ route('projects.campaign-tests.store', $project) }}">
                    @csrf
                    <p><label>投放平台 <select name="platform"><option value="facebook">Facebook</option><option value="tiktok">TikTok</option><option value="other">其他</option></select></label></p>
                    <p><label>广告系列名称 <input name="campaign_name" required></label></p>
                    <p><label>花费 <input name="spend" type="number" min="0" step="0.01" required></label></p>
                    <p><label>展示次数 <input name="impressions" type="number" min="0" required></label></p>
                    <p><label>点击次数 <input name="clicks" type="number" min="0" required></label></p>
                    <p><label>转化次数 <input name="conversions" type="number" min="0" required></label></p>
                    <p><label>反馈给 <select name="feedback_target_stage"><option value="">暂不创建反馈</option><option value="website_operations">网站运营部（落地页/价格/规格）</option><option value="content_creative">内容创意部（素材）</option><option value="market_research">市场研究部（选品/SKU）</option></select></label></p>
                    <p><label>优化反馈 <textarea name="feedback_note"></textarea></label></p>
                    <p><button type="submit">保存测试数据</button></p>
                </form>
            </section>
        @endif

        @if (auth()->user()?->department?->code === 'content_creative')
            <section>
                <h2>上传素材</h2>
                <form method="post" action="{{ route('projects.creative-assets.store', $project) }}" enctype="multipart/form-data">
                    @csrf
                    <p><label>素材名称 <input name="title" required></label></p>
                    <p><label>素材类型 <select name="asset_type"><option value="video">视频</option><option value="image">图片</option><option value="copy">文案</option></select></label></p>
                    <p><label>素材来源 <select name="source_type"><option value="original">原创</option><option value="tiktok">TikTok</option><option value="amazon">Amazon</option><option value="other">其他</option></select></label></p>
                    <p><label>上传文件 <input name="asset_file" type="file"></label></p>
                    <p><label>外部素材链接 <input name="external_url" type="url"></label></p>
                    <p><label>文案内容 <textarea name="copy_text"></textarea></label></p>
                    <p><label>关联落地页 <select name="landing_page_id"><option value="">暂不关联</option>@foreach ($project->landingPages as $page)<option value="{{ $page->id }}">V{{ $page->version }} · {{ $page->title }}</option>@endforeach</select></label></p>
                    <p><label>创作备注 <textarea name="notes"></textarea></label></p>
                    <p><button type="submit">保存素材草稿</button></p>
                </form>
            </section>
        @endif

        <section>
            <h2>落地页版本</h2>
            <ul>
                @forelse ($project->landingPages as $page)
                    <li>V{{ $page->version }} · {{ $page->title }} · {{ $page->status }} · <a href="{{ $page->page_url }}" target="_blank" rel="noreferrer">打开页面</a></li>
                @empty
                    <li>暂未创建落地页版本</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2>创意素材</h2>
            <ul>
                @forelse ($project->creativeAssets as $asset)
                    <li>{{ $asset->title }} · {{ $asset->asset_type }} · {{ $asset->source_type }} · {{ $asset->status }}</li>
                @empty
                    <li>暂未录入创意素材</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2>投放测试结果</h2>
            <ul>
                @forelse ($project->campaignTests as $campaign)
                    <li>{{ $campaign->platform }} · {{ $campaign->campaign_name }} · 花费 {{ $campaign->spend }} · CTR {{ $campaign->ctr }}% · 转化 {{ $campaign->conversions }}</li>
                @empty
                    <li>暂未录入投放测试</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2>优化反馈</h2>
            <ul>
                @forelse ($project->optimizationFeedback as $feedback)
                    <li>
                        <p>发送至 {{ $feedback->target_stage }} · {{ $feedback->status }} · {{ $feedback->note }}</p>
                        @if ($feedback->response_note)
                            <p>处理说明：{{ $feedback->response_note }}</p>
                        @endif
                        @if (auth()->user()?->department?->code === $feedback->target_stage && $feedback->status !== 'resolved')
                            <form method="post" action="{{ route('projects.optimization-feedback.update', [$project, $feedback]) }}">
                                @csrf
                                @method('PATCH')
                                <label>处理状态 <select name="status"><option value="in_progress">处理中</option><option value="resolved">已解决</option></select></label>
                                <label>处理说明 <textarea name="response_note" required></textarea></label>
                                <button type="submit">处理反馈</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li>暂无优化反馈</li>
                @endforelse
            </ul>
        </section>
    </main>
</body>
</html>
