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
    </main>
</body>
</html>
