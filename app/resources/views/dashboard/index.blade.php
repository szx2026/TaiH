<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>工作看板 · 跨境产品 ERP</title>
</head>
<body>
    <main>
        <nav><a href="{{ route('projects.index') }}">产品项目池</a></nav>
        <h1>{{ $isAdministrator ? '全局工作看板' : '我的工作看板' }}</h1>
        <section>
            <h2>投放摘要</h2>
            <p>花费：{{ $metrics->spend }} · 展示：{{ $metrics->impressions }} · 点击：{{ $metrics->clicks }} · 转化：{{ $metrics->conversions }}</p>
        </section>
        <section>
            <h2>当前环节项目</h2>
            <ul>
                @forelse ($projects as $project)
                    <li><a href="{{ route('projects.show', $project) }}">{{ $project->product_name }}</a> · {{ $project->current_stage }} · {{ $project->status }}</li>
                @empty
                    <li>当前没有需要处理的项目。</li>
                @endforelse
            </ul>
        </section>
        <section>
            <h2>待处理优化反馈</h2>
            <ul>
                @forelse ($feedback as $item)
                    <li>{{ $item->project->product_name }} · {{ $item->note }}</li>
                @empty
                    <li>当前没有待处理反馈。</li>
                @endforelse
            </ul>
        </section>
    </main>
</body>
</html>
