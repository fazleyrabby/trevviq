@extends('layouts.frontend')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-12 sm:py-16">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" class="h-14 w-14 rounded-full object-cover"/>
                @else
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-teal-500 text-lg font-bold text-white">
                        {{ $user->initials() }}
                    </span>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-white">Welcome back, {{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $user->role->label() }} &middot; Joined {{ $user->created_at->format('F Y') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5">
                Edit profile
            </a>
        </div>

        @if (! $user->email_verified_at)
            <div class="mt-8 flex flex-col gap-3 rounded-xl border border-teal-500/30 bg-teal-500/10 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-teal-200">Verify your email address</p>
                    <p class="mt-0.5 text-sm text-teal-300/80">Confirm your email to secure your account and unlock every feature.</p>
                </div>
                <a href="{{ route('verification.notice') }}"
                   class="inline-flex shrink-0 items-center justify-center rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-400">
                    Verify email
                </a>
            </div>
        @endif

        <h2 class="mt-12 text-sm font-semibold uppercase tracking-wide text-slate-500">Quick links</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <a href="{{ route('home') }}"
               class="group rounded-xl border border-white/10 bg-white/5 p-5 transition hover:border-teal-500/40 hover:bg-white/10">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/15 text-teal-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 1.5a6.5 6.5 0 015.41 10.13l-4.16-4.16V6a.75.75 0 00-1.5 0v3.47L5.59 13.63A6.5 6.5 0 0110 3.5z"/>
                    </svg>
                </span>
                <p class="mt-4 font-semibold text-white group-hover:text-teal-200">Explore</p>
                <p class="mt-1 text-sm text-slate-400">Discover destinations and stories from real travellers.</p>
            </a>

            <a href="{{ route('profile.show') }}"
               class="group rounded-xl border border-white/10 bg-white/5 p-5 transition hover:border-teal-500/40 hover:bg-white/10">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/15 text-teal-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8zM3 18a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <p class="mt-4 font-semibold text-white group-hover:text-teal-200">Profile</p>
                <p class="mt-1 text-sm text-slate-400">See your public profile as others see it.</p>
            </a>

            <a href="{{ route('profile.edit') }}"
               class="group rounded-xl border border-white/10 bg-white/5 p-5 transition hover:border-teal-500/40 hover:bg-white/10">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/15 text-teal-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-8.5 8.5a1 1 0 01-.44.256l-3 .857a.75.75 0 01-.93-.93l.857-3a1 1 0 01.256-.44l8.5-8.5z"/>
                    </svg>
                </span>
                <p class="mt-4 font-semibold text-white group-hover:text-teal-200">Edit profile</p>
                <p class="mt-1 text-sm text-slate-400">Update your name, bio, avatar, and more.</p>
            </a>
        </div>

        @if ($user->isViewer())
            <div class="mt-12 rounded-xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Become a traveller</h2>
                <p class="mt-2 max-w-2xl text-sm text-slate-400">
                    Travellers can upload videos and write reviews, turning the places they have actually been into guides for everyone else.
                </p>
                <form method="POST" action="{{ route('profile.become-traveller') }}" class="mt-4">
                    @csrf
                    <button type="submit"
                            class="rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                        Become a traveller
                    </button>
                </form>
            </div>
        @else
            <div class="mt-12 rounded-xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Your activity</h2>
                <p class="mt-2 text-sm text-slate-400">
                    You have not shared anything yet. Videos and reviews you publish will appear here.
                </p>
            </div>
        @endif
    </section>
@endsection
