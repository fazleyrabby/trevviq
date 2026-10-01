@extends('layouts.frontend')

@section('title', $user->name.' — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-12 sm:py-16">
        @if (session('status') === 'profile-updated')
            <div class="mb-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                Your profile has been updated.
            </div>
        @elseif (session('status') === 'now-a-traveller')
            <div class="mb-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                You are now a traveller. You can upload videos and write reviews.
            </div>
        @endif

        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-center gap-4">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" class="h-20 w-20 rounded-full object-cover"/>
                    @else
                        <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-teal-500 text-2xl font-bold text-white">
                            {{ $user->initials() }}
                        </span>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                        <p class="mt-0.5 text-sm text-slate-400">&#64;{{ $user->username }}</p>
                        <p class="mt-1 inline-flex items-center rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-medium text-teal-300">
                            {{ $user->role->label() }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5">
                    Edit profile
                </a>
            </div>

            <dl class="mt-8 grid gap-6 sm:grid-cols-2">
                @if ($user->country_code)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Country</dt>
                        <dd class="mt-1 text-sm text-slate-200">{{ $user->country_code }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Joined</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $user->created_at->format('F Y') }}</dd>
                </div>
            </dl>

            <div class="mt-8">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bio</h2>
                @if ($user->bio)
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $user->bio }}</p>
                @else
                    <p class="mt-2 text-sm text-slate-500">No bio yet.</p>
                @endif
            </div>

            @if ($user->isViewer())
                <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-5">
                    <h2 class="text-base font-semibold text-white">Become a traveller</h2>
                    <p class="mt-1.5 text-sm text-slate-400">
                        Travellers can upload videos and write reviews about the places they have actually been.
                    </p>
                    <form method="POST" action="{{ route('profile.become-traveller') }}" class="mt-4">
                        @csrf
                        <button type="submit"
                                class="rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                            Become a traveller
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </section>
@endsection
