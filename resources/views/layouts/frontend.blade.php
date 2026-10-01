<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Discover the world through people who have actually been there.')"/>
    <link rel="canonical" href="@yield('canonical', url()->current())"/>
    @hasSection('robots')
        <meta name="robots" content="@yield('robots')"/>
    @endif

    <meta property="og:type" content="@yield('og_type', 'website')"/>
    <meta property="og:site_name" content="{{ config('app.name') }}"/>
    <meta property="og:title" content="@yield('title', config('app.name'))"/>
    <meta property="og:description" content="@yield('description', 'Discover the world through people who have actually been there.')"/>
    <meta property="og:url" content="@yield('canonical', url()->current())"/>
    <meta name="twitter:card" content="summary_large_image"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if(config('trevviq.analytics.umami_website_id'))
        <script defer src="{{ config('trevviq.analytics.umami_script_url', 'https://analytics.umami.is/script.js') }}" data-website-id="{{ config('trevviq.analytics.umami_website_id') }}"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <header class="border-b border-white/5">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500 text-sm font-black text-white">R</span>
                {{ config('app.name') }}
            </a>
            <div class="flex items-center gap-2 text-sm text-slate-300 sm:gap-4">
                <a href="{{ route('explore.index') }}" class="hidden rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 sm:inline">
                    Explore
                </a>
                <a href="{{ route('travel.index') }}" class="hidden rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 sm:inline">
                    Destinations
                </a>
                <a href="{{ route('search.index') }}" class="hidden rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 sm:inline">
                    Search
                </a>
                <a href="{{ route('videos.index') }}" class="hidden rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 sm:inline">
                    Videos
                </a>
                <a href="{{ route('events.index') }}" class="hidden rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 sm:inline">
                    Events
                </a>

                <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-white/10 px-3 py-1.5 text-slate-200 transition hover:bg-white/5">
                    Admin
                </a>

                @guest
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-teal-500 px-3 py-1.5 font-semibold text-white transition hover:bg-teal-400">
                        Sign up
                    </a>
                @endguest

                @auth
                    <a href="{{ route('dashboard') }}" class="hidden rounded-lg px-3 py-1.5 text-slate-200 transition hover:bg-white/5 sm:inline">
                        Dashboard
                    </a>

                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button"
                                class="flex items-center gap-2 rounded-lg border border-white/10 px-2 py-1.5 text-slate-200 transition hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-teal-500"
                                aria-haspopup="true"
                                :aria-expanded="open"
                                @click="open = !open">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-500 text-xs font-bold text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                            <span class="hidden max-w-[10rem] truncate sm:inline">{{ auth()->user()->name }}</span>
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition.origin.top.right
                             @click.outside="open = false"
                             class="absolute right-0 z-50 mt-2 w-52 overflow-hidden rounded-xl border border-white/10 bg-slate-900 shadow-xl">
                            <div class="border-b border-white/5 px-4 py-3">
                                <p class="truncate text-sm font-medium text-slate-100">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">
                                Profile
                            </a>
                            <a href="{{ route('travel-history.index') }}" class="block px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">
                                Travel history
                            </a>
                            <a href="{{ route('reviews.index') }}" class="block px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">
                                My reviews
                            </a>
                            <a href="{{ route('saved.index') }}" class="block px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">
                                Saved places
                            </a>
                            <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white flex items-center justify-between">
                                Notifications
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="inline-flex items-center rounded-full bg-teal-500/20 px-2 py-0.5 text-[10px] font-medium text-teal-300">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-white/5 py-10">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
            <p class="text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Discover the world through people who have actually been there.
            </p>
            <nav class="flex gap-4 text-xs font-medium text-slate-400">
                <a href="{{ route('legal.terms') }}" class="hover:text-teal-300">Terms</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-teal-300">Privacy</a>
                <a href="{{ route('legal.acceptable-use') }}" class="hover:text-teal-300">Acceptable Use</a>
                <a href="{{ route('legal.dmca') }}" class="hover:text-teal-300">DMCA</a>
            </nav>
        </div>
    </footer>
</body>
</html>
