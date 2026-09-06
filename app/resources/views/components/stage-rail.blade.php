@props(['stage'])

@php($stages = ['market_research' => '选品', 'website_operations' => '页面', 'content_creative' => '素材', 'traffic_growth' => '投放'])
@php($activeClasses = ['market_research' => 'bg-orange-100 text-orange-800', 'website_operations' => 'bg-blue-100 text-blue-800', 'content_creative' => 'bg-yellow-100 text-yellow-900', 'traffic_growth' => 'bg-violet-100 text-violet-800'])

<div class="flex items-center gap-1.5" aria-label="项目阶段">
    @foreach ($stages as $code => $label)
        <span @class(['rounded-full px-2 py-1 text-[11px] font-semibold', $activeClasses[$code] => $stage === $code, 'bg-zinc-100 text-zinc-400' => $stage !== $code])>{{ $label }}</span>
    @endforeach
</div>
