@props(['label', 'value', 'hint' => null])
<section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-medium text-slate-500">{{ $label }}</p><p class="mt-2 text-2xl font-bold">{{ $value }}</p>@if ($hint)<p class="mt-2 text-xs text-emerald-600">{{ $hint }}</p>@endif</section>
