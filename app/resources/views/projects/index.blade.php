<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品项目池</title>
</head>
<body>
    <main>
        <h1>产品项目池</h1>
        <form method="post" action="{{ route('projects.store') }}">
            @csrf
            <label>产品名称 <input name="product_name" required></label>
            <label>产品类目 <input name="category"></label>
            <label>目标市场 <input name="market" value="US" required></label>
            <label>优先级
                <select name="priority">
                    <option value="high">高</option>
                    <option value="medium" selected>中</option>
                    <option value="low">低</option>
                </select>
            </label>
            <button type="submit">新建产品项目</button>
        </form>

        <ul>
            @forelse ($projects as $project)
                <li>{{ $project->project_code }} · {{ $project->product_name }} · {{ $project->market }}</li>
            @empty
                <li>暂无产品项目</li>
            @endforelse
        </ul>
    </main>
</body>
</html>
