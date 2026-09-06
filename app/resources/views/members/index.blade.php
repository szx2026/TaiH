<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>成员管理 · NC ERP</title>
</head>
<body>
    <main>
        <p><a href="{{ route('projects.index') }}">返回产品项目池</a></p>
        <h1>成员管理</h1>
        <section>
            <h2>新增成员</h2>
            <form method="post" action="{{ route('members.store') }}">
                @csrf
                <p><label>姓名 <input name="name" required></label></p>
                <p><label>邮箱 <input name="email" type="email" required></label></p>
                <p><label>初始密码 <input name="password" type="password" required></label></p>
                <p><label>部门 <select name="department_id" required>@foreach ($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></label></p>
                <p><label>角色 <select name="role"><option value="member">成员</option><option value="manager">部门负责人</option><option value="administrator">管理员</option></select></label></p>
                <button type="submit">创建成员</button>
            </form>
        </section>
        <section>
            <h2>现有成员</h2>
            <ul>
                @foreach ($members as $member)
                    <li>{{ $member->name }} · {{ $member->email }} · {{ $member->department?->name ?? '未分配部门' }} · {{ $member->role }}</li>
                @endforeach
            </ul>
        </section>
    </main>
</body>
</html>
