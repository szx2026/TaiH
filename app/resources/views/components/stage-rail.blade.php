@props(['stage'])

@php($stages = ['market_research' => '选品', 'website_operations' => '页面', 'content_creative' => '素材', 'traffic_growth' => '投放'])

<div class="flex items-center gap-1.5" aria-label="项目阶段">
    @foreach ($stages as $code => $label)
        <span @class(['rounded-full px-2 py-1 text-[11px] font-semibold', 'bg-blue-100 text-blue-700' => $stage === $code, 'bg-slate-100 text-slate-400' => $stage !== $code])>{{ $label }}</span>
    @endforeach
</div>
