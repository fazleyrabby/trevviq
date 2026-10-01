<!-- Admin Sidebar -->
<aside class="app-sidebar" :class="{ 'is-open': sidebarOpen }">

    <!-- Brand Header -->
    <div class="app-sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none">
            <span class="d-flex align-items-center justify-content-center fw-bold rounded-3"
                  style="width: 34px; height: 34px; background: var(--roam-accent); font-size: 15px;">R</span>
            <span class="d-flex flex-column" style="line-height: 1.1;">
                <span style="font-size: 14px; font-weight: 800; letter-spacing: 0.05em; color: #ffffff; text-transform: uppercase;">
                    {{ config('app.name') }} <span style="color: var(--roam-accent);">Admin</span>
                </span>
                <span style="font-size: 10px; color: var(--roam-sidebar-muted); font-family: monospace; margin-top: 1px;">Discovery Platform</span>
            </span>
        </a>
        <button @click="sidebarOpen = false"
                type="button"
                class="d-lg-none text-white border-0 bg-transparent p-1"
                style="cursor: pointer; background: transparent; border: 0; color: var(--roam-sidebar-muted);"
                aria-label="Close sidebar">
            <i data-lucide="x" style="width: 20px; height: 20px;"></i>
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="app-sidebar-nav">
        <div class="nav-group-title">Overview</div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard" style="width: 17px; height: 17px; flex-shrink: 0;"></i>
            <span style="flex: 1;">Dashboard</span>
        </a>

        <div class="nav-group-title">Discovery</div>

        @php
            $discoveryModules = [
                ['icon' => 'map-pin', 'label' => 'Locations', 'route' => 'admin.locations.index', 'pattern' => 'admin.locations.*'],
                ['icon' => 'video', 'label' => 'Videos', 'route' => 'admin.videos.index', 'pattern' => 'admin.videos.*'],
                ['icon' => 'upload', 'label' => 'Imports', 'route' => 'admin.imports.index', 'pattern' => 'admin.imports.*'],
            ];
        @endphp

        @foreach ($discoveryModules as $module)
            <a href="{{ route($module['route']) }}"
               class="nav-item-link {{ request()->routeIs($module['route']) || request()->routeIs($module['pattern']) ? 'active' : '' }}">
                <i data-lucide="{{ $module['icon'] }}" style="width: 17px; height: 17px; flex-shrink: 0;"></i>
                <span style="flex: 1;">{{ $module['label'] }}</span>
            </a>
        @endforeach

        <a href="{{ route('admin.events.index') }}"
           class="nav-item-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
            <i data-lucide="calendar-days" style="width: 17px; height: 17px; flex-shrink: 0;"></i>
            <span style="flex: 1;">Events</span>
        </a>

        <div class="nav-group-title">Community</div>

        @php
            $communityModules = [
                ['icon' => 'users', 'label' => 'Users', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*'],
                ['icon' => 'star', 'label' => 'Reviews', 'route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*'],
                ['icon' => 'flag', 'label' => 'Reports', 'route' => 'admin.reports.index', 'pattern' => 'admin.reports.*'],
            ];
        @endphp

        @foreach ($communityModules as $module)
            <a href="{{ route($module['route']) }}"
               class="nav-item-link {{ request()->routeIs($module['route']) || request()->routeIs($module['pattern']) ? 'active' : '' }}">
                <i data-lucide="{{ $module['icon'] }}" style="width: 17px; height: 17px; flex-shrink: 0;"></i>
                <span style="flex: 1;">{{ $module['label'] }}</span>
            </a>
        @endforeach

        <div class="nav-group-title">System</div>

        <span class="nav-item-link is-disabled" title="Coming soon">
            <i data-lucide="settings" style="width: 17px; height: 17px; flex-shrink: 0;"></i>
            <span style="flex: 1;">Settings</span>
            <span class="badge bg-secondary-lt px-2 py-0" style="font-size: 10px;">Soon</span>
        </span>
    </div>

    <!-- Admin User Footer -->
    <div class="app-sidebar-user">
        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
             style="width: 32px; height: 32px; min-width: 32px; font-size: 13px; background: var(--roam-accent);">
            {{ substr(auth('admin')->user()->name ?? 'A', 0, 1) }}
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="font-size: 12px; font-weight: 600; color: #ffffff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2;">
                {{ auth('admin')->user()->name ?? 'Administrator' }}
            </div>
            <div style="font-size: 11px; color: var(--roam-sidebar-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2; margin-top: 2px;">
                {{ auth('admin')->user()->email ?? '' }}
            </div>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST" data-confirm="Are you sure you want to log out of the admin panel?" data-confirm-title="Confirm Logout" data-confirm-btn="Yes, Log Out" data-confirm-icon="question" class="m-0 flex-shrink-0">
            @csrf
            <button type="submit" title="Logout"
                    style="background: transparent; border: 0; color: var(--roam-sidebar-muted); cursor: pointer; padding: 4px; border-radius: 4px;"
                    onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--roam-sidebar-muted)'">
                <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
            </button>
        </form>
    </div>
</aside>
