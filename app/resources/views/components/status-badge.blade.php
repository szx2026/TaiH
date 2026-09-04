@props(['status'])
@php($labels = ['draft' => '草稿', 'in_progress' => '进行中', 'blocked' => '已阻塞', 'approved' => '已通过', 'rejected' => '已淘汰', 'archived' => '已归档'])
<span class="inline-flex rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $labels[$status] ?? $status }}</span>
