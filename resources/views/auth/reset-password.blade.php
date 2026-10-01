@extends('layouts.frontend')

@section('title', 'Choose a new password — '.config('app.name'))

@section('content')
    <section class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-6 py-16 sm:py-24">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Choose a new password</h1>
            <p class="mt-2 text-sm text-slate-400">Pick something strong that you have not used before.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}"/>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email address</label>
                    <input id="email"
                           name="email"
                           type="email"
                           value="{{ old('email', $email) }}"
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
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">New password</label>
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
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-300">Confirm new password</label>
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           autocomplete="new-password"
                           required
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                           placeholder="Repeat your new password"/>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Reset password
                </button>
            </form>
        </div>
    </section>
@endsection
