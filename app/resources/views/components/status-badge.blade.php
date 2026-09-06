@props(['status'])
@php($labels = ['draft' => '草稿', 'in_progress' => '进行中', 'blocked' => '已阻塞', 'approved' => '已通过', 'rejected' => '已淘汰', 'archived' => '已归档'])
<span @class(['inline-flex rounded-md border px-2 py-1 text-xs font-semibold', 'status-success' => in_array($status, ['approved']), 'status-danger' => in_array($status, ['blocked', 'rejected']), 'border-slate-200 bg-slate-100 text-slate-700' => ! in_array($status, ['approved', 'blocked', 'rejected'])])>{{ $labels[$status] ?? $status }}</span>
