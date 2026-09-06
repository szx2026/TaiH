@props(['label', 'value', 'hint' => null])
<section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm"><p class="text-xs font-medium text-zinc-500">{{ $label }}</p><p class="mt-2 text-2xl font-bold">{{ $value }}</p>@if ($hint)<p class="mt-2 text-xs text-zinc-500">{{ $hint }}</p>@endif</section>
