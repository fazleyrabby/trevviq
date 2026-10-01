<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta name="robots" content="noindex,nofollow"/>
    <title>Admin Sign In - {{ config('app.name') }}</title>

    <link href="{{ asset('tabler/dist/css/tabler.min.css') }}" rel="stylesheet"/>
    @vite(['resources/css/app.css'])

    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at top left, #0f172a, #020617 60%);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .login-card { width: 100%; max-width: 420px; }
    </style>
</head>
<body>
<div class="login-card px-3">
    <div class="text-center text-white mb-4">
        <span class="d-inline-flex align-items-center justify-content-center fw-bold rounded-3 mb-3"
              style="width: 52px; height: 52px; background: #0d9488; font-size: 22px;">R</span>
        <h1 class="h2 mb-1">{{ config('app.name') }} Admin</h1>
        <p class="text-white-50 mb-0">Sign in to manage the platform.</p>
    </div>

    <div class="card card-md">
        <div class="card-body">
            <h2 class="h3 text-center mb-4">Administrator login</h2>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" autocomplete="off" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="admin@roam.test"
                           required
                           autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label for="password" class="form-label">Password</label>
                    <input id="password"
                           type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Your password"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-check">
                        <input type="checkbox" name="remember" value="1" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                        <span class="form-check-label">Remember me on this device</span>
                    </label>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100" style="background-color: #0d9488; border-color: #0d9488;">Sign in</button>
                </div>
            </form>
        </div>
    </div>

    @if (config('roam.demo.enabled'))
        <div class="mt-3">
            <form method="POST" action="{{ route('admin.demo-login') }}">
                @csrf
                <button type="submit" class="btn w-100" style="border:1px solid #0d9488; color:#5eead4; background: rgba(13,148,136,0.12);">
                    Sign in as demo admin
                </button>
            </form>
            <p class="text-center text-white-50 small mt-2 mb-0">
                {{ config('roam.demo.admin_email') }} &middot; password
            </p>
        </div>
    @endif

    <p class="text-center text-white-50 mt-3 mb-0 small">
        Protected area &middot; Unauthorized access is prohibited.
    </p>
</div>

<script src="{{ asset('tabler/dist/js/tabler.min.js') }}"></script>
</body>
</html>
