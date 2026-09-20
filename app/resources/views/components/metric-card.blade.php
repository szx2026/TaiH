@props(['label', 'value', 'hint' => null])
<section class="metric-card-smooth rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition hover:border-slate-300">
    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
            <span class="inline-block h-1.5 w-1.5 rounded-full bg-teal-400"></span>
            <span>{{ $hint }}</span>
        </p>
    @endif
</section>

