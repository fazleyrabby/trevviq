@extends('layouts.frontend')

@section('title', 'Reset your password — '.config('app.name'))

@section('content')
    <section class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-6 py-16 sm:py-24">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Reset your password</h1>
            <p class="mt-2 text-sm text-slate-400">
                Enter your email address and we will send you a link to choose a new password.
            </p>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
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

                <button type="submit"
                        class="w-full rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Email password reset link
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-400">
                Remembered it?
                <a href="{{ route('login') }}" class="font-medium text-teal-300 transition hover:text-teal-200">Back to log in</a>
            </p>
        </div>
    </section>
@endsection
