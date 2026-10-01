@extends('layouts.frontend')

@section('title', 'Log in — '.config('app.name'))

@section('content')
    <section class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-6 py-16 sm:py-24">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Welcome back</h1>
            <p class="mt-2 text-sm text-slate-400">Log in to pick up where you left off.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email address</label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           autofocus
                           required
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           @error('email') aria-describedby="email-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500/50 @enderror"
                           placeholder="you@example.com"/>
                    @error('email')
                        <p id="email-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-teal-300 transition hover:text-teal-200">
                            Forgot password?
                        </a>
                    </div>
                    <input id="password"
                           name="password"
                           type="password"
                           autocomplete="current-password"
                           required
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           @error('password') aria-describedby="password-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-red-500/50 @enderror"
                           placeholder="Your password"/>
                    @error('password')
                        <p id="password-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox"
                           name="remember"
                           value="1"
                           @checked(old('remember'))
                           class="h-4 w-4 rounded border-white/20 bg-white/5 text-teal-500 focus:ring-2 focus:ring-teal-500 focus:ring-offset-0"/>
                    Remember me on this device
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Log in
                </button>
            </form>

            @if (config('roam.demo.enabled'))
                <div class="mt-6 border-t border-white/10 pt-6">
                    <p class="mb-3 text-center text-xs uppercase tracking-wide text-slate-500">Or try the demo</p>
                    <form method="POST" action="{{ route('demo.login') }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-lg border border-teal-500/40 bg-teal-500/10 px-4 py-2.5 text-sm font-semibold text-teal-200 transition hover:bg-teal-500/20 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            Log in as demo traveller
                        </button>
                    </form>
                    <p class="mt-2 text-center text-xs text-slate-500">
                        {{ config('roam.demo.user_email') }} · password
                    </p>
                </div>
            @endif

            <p class="mt-6 text-center text-sm text-slate-400">
                New here?
                <a href="{{ route('register') }}" class="font-medium text-teal-300 transition hover:text-teal-200">Create an account</a>
            </p>
        </div>
    </section>
@endsection
