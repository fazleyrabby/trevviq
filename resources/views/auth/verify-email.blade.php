@extends('layouts.frontend')

@section('title', 'Verify your email — '.config('app.name'))

@section('content')
    <section class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-6 py-16 sm:py-24">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Verify your email</h1>
            <p class="mt-2 text-sm text-slate-400">
                We sent a verification link to your email address. Click the link to confirm your account and unlock everything.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                    A fresh verification link has been sent to your email address.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                @csrf
                <button type="submit"
                        class="w-full rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Resend verification email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit"
                        class="w-full rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    Log out
                </button>
            </form>
        </div>
    </section>
@endsection
