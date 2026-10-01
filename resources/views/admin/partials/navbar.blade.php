<!-- Admin Topbar -->
<header class="app-topbar">
    <button @click="sidebarOpen = true"
            type="button"
            class="btn btn-icon btn-ghost-secondary d-lg-none"
            aria-label="Open sidebar">
        <i data-lucide="menu" style="width: 20px; height: 20px;"></i>
    </button>

    <div class="flex-fill">
        <h1 class="h3 mb-0">@yield('page-title', 'Dashboard')</h1>
        @hasSection('page-subtitle')
            <div class="text-secondary small mt-1">@yield('page-subtitle')</div>
        @endif
    </div>

    <div class="d-flex align-items-center gap-2">
        @hasSection('page-actions')
            @yield('page-actions')
        @endif

        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
            <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
            <span class="d-none d-sm-inline ms-1">View site</span>
        </a>
    </div>
</header>
