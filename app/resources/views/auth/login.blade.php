<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 · 跨境产品 ERP</title>
</head>
<body>
    <main>
        <h1>跨境产品 ERP</h1>
        <h2>登录</h2>
        @if ($errors->any())
            <p>{{ $errors->first() }}</p>
        @endif
        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <p><label>邮箱 <input name="email" type="email" value="{{ old('email') }}" required autofocus></label></p>
            <p><label>密码 <input name="password" type="password" required></label></p>
            <p><label><input name="remember" type="checkbox" value="1"> 保持登录</label></p>
            <button type="submit">登录</button>
        </form>
    </main>
</body>
</html>
