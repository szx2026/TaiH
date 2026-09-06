<x-layouts.app title="数据接入 · 跨境产品 ERP">
    <div class="mb-7"><p class="text-sm font-semibold text-blue-600">数据接入</p><h1 class="mt-1 text-3xl font-bold">自动化数据接入</h1><p class="mt-2 text-sm text-slate-500">保留 Facebook 广告、Shopify 与外部趋势数据的接口接入位置；当前各部门仍可随时手动录入。</p></div>
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach(['Facebook 广告数据' => '待配置授权后同步投放数据', 'Shopify' => '待配置店铺授权后同步产品与页面', '选品趋势来源' => '待配置合规接口或手动导入', '对象存储' => '待配置素材文件存储服务'] as $name => $description)
            <div class="rounded-xl border border-slate-200 bg-white p-5"><h2 class="font-semibold">{{ $name }}</h2><p class="mt-2 text-sm text-slate-600">{{ $description }}</p><span class="mt-4 inline-block rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">尚未连接</span></div>
        @endforeach
    </section>
</x-layouts.app>
