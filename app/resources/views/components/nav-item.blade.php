@props(['label', 'href' => '#', 'active' => false, 'count' => null, 'tone' => 'slate'])
@php
    $dotColors = [
        'market_research' => 'bg-orange-400',
        'website_operations' => 'bg-sky-400',
        'content_creative' => 'bg-amber-400',
        'traffic_growth' => 'bg-violet-400',
        'slate' => 'bg-slate-400',
    ];
    $dotColor = $dotColors[$tone] ?? 'bg-slate-400';
@endphp
<a href="{{ $href }}" @class([
    'group mb-0.5 flex items-center rounded-lg px-3 py-2 text-xs font-medium transition-all duration-150',
    'bg-teal-500/15 text-teal-300 font-semibold border-l-2 border-teal-400 shadow-xs' => $active,
    'text-slate-400 hover:bg-white/[0.05] hover:text-slate-200' => ! $active,
])>
    <span class="h-2 w-2 rounded-full transition-transform duration-200 {{ $dotColor }} {{ $active ? 'scale-110 shadow-[0_0_6px_rgba(45,212,191,0.6)]' : 'opacity-60 group-hover:opacity-100 group-hover:scale-110' }}"></span>
    <span class="ml-2.5 tracking-wide">{{ $label }}</span>
    @if ($count)
        <span @class([
            'ml-auto rounded-full px-2 py-0.5 text-[10px] font-bold transition-all',
            'bg-teal-900/80 text-teal-300 border border-teal-500/40' => $active,
            'bg-slate-800 text-slate-400 border border-slate-700/60 group-hover:text-slate-300' => ! $active,
        ])>{{ $count }}</span>
    @endif
</a>
