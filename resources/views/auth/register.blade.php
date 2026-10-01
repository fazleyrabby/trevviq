@extends('layouts.frontend')

@section('title', 'Sign up — '.config('app.name'))

@section('content')
    <section class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-6 py-16 sm:py-24">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Create your account</h1>
            <p class="mt-2 text-sm text-slate-400">It takes under a minute — then start exploring the real world.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Full name</label>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name') }}"
                           autocomplete="name"
                           autofocus
                           required
                           aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                           @error('name') aria-describedby="name-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-red-500/50 @enderror"
                           placeholder="Alex Morgan"/>
                    @error('name')
                        <p id="name-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-300">Username</label>
                    <input id="username"
                           name="username"
                           type="text"
                           value="{{ old('username') }}"
                           autocomplete="off"
                           required
                           aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                           @error('username') aria-describedby="username-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('username') border-red-500/50 @enderror"
                           placeholder="alexmorgan"/>
                    @error('username')
                        <p id="username-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email address</label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           autocomplete="email"
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
                    <label for="country_code" class="mb-1.5 block text-sm font-medium text-slate-300">Country code (optional)</label>
                    <input id="country_code"
                           name="country_code"
                           type="text"
                           value="{{ old('country_code') }}"
                           maxlength="2"
                           autocomplete="country"
                           aria-invalid="{{ $errors->has('country_code') ? 'true' : 'false' }}"
                           @error('country_code') aria-describedby="country_code-error" @enderror
                           class="w-full max-w-[8rem] rounded-lg border border-white/10 bg-white/5 px-3 py-2 uppercase text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('country_code') border-red-500/50 @enderror"
                           placeholder="US"/>
                    @error('country_code')
                        <p id="country_code-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">Password</label>
                    <input id="password"
                           name="password"
                           type="password"
                           autocomplete="new-password"
                           required
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           @error('password') aria-describedby="password-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-red-500/50 @enderror"
                           placeholder="At least 8 characters"/>
                    @error('password')
                        <p id="password-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-300">Confirm password</label>
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           autocomplete="new-password"
                           required
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Repeat your password"/>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Create account
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-teal-300 transition hover:text-teal-200">Log in</a>
            </p>
        </div>
    </section>
@endsection
