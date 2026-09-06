<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 · NC ERP</title>
    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="login-page">
    <section class="login-intro" aria-labelledby="login-brand-heading">
        <a class="login-brand" href="{{ route('login') }}" aria-label="NC ERP 登录页">
            <span class="login-mark" aria-hidden="true">NC</span>
            <span class="login-brand-copy">
                <span id="login-brand-heading" class="login-brand-name">NC ERP</span>
                <span class="login-brand-tagline">跨境产品增长协作系统</span>
            </span>
        </a>

        <div class="login-statement">
            <p class="login-kicker">One workspace · four teams</p>
            <h1 class="login-statement-line">越努力，越幸运</h1>
            <p>从产品研究到广告测试，在同一个工作空间里完成协作、沉淀与推进。</p>
        </div>

        <p class="login-intro-footer">已启用安全登录</p>
    </section>

    <main class="login-panel">
        <section class="login-card" aria-labelledby="login-heading">
            <div class="login-card-heading">
                <p class="login-card-eyebrow">NC ERP / Workspace</p>
                <h2 id="login-heading">登录工作台</h2>
                <p class="login-card-description">使用你的部门账号进入协作空间。</p>
            </div>

            @if ($errors->any())
                <p class="login-alert" role="alert">{{ $errors->first() }}</p>
            @endif

            <form class="login-form" method="post" action="{{ route('login.store') }}">
            @csrf
                <div class="login-field">
                    <label for="email">邮箱</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus autocomplete="email">
                </div>
                <div class="login-field">
                    <label for="password">密码</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <label class="login-remember"><input name="remember" type="checkbox" value="1"> 保持登录</label>
                <button class="login-submit" type="submit">进入工作台</button>
            </form>

            <p class="login-security-note">仅限已获授权的团队成员使用。</p>
        </section>
    </main>
</body>
</html>
