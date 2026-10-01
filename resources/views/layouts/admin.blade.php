<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="robots" content="noindex,nofollow"/>
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }} Admin</title>

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tabler CSS -->
    <link href="{{ asset('tabler/dist/css/tabler.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/dist/css/tabler-vendors.min.css') }}" rel="stylesheet"/>

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
        }

        :root {
            --roam-accent: #0d9488;
            --roam-accent-strong: #0f766e;
            --roam-sidebar-bg: #0f172a;
            --roam-sidebar-border: #1e293b;
            --roam-sidebar-muted: #94a3b8;
        }

        .app-shell { display: flex; height: 100vh; width: 100vw; overflow: hidden; position: relative; }
        .app-sidebar {
            width: 260px; min-width: 260px; max-width: 260px; height: 100vh;
            background-color: var(--roam-sidebar-bg); border-right: 1px solid var(--roam-sidebar-border);
            color: #f1f5f9; display: flex; flex-direction: column; flex-shrink: 0;
            z-index: 1040; transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .app-sidebar-brand {
            height: 64px; min-height: 64px; background-color: #090d16;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px; border-bottom: 1px solid var(--roam-sidebar-border);
        }
        .app-sidebar-nav { flex: 1; overflow-y: auto; padding: 10px 0; }
        .app-sidebar-nav::-webkit-scrollbar { width: 6px; }
        .app-sidebar-nav::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .nav-group-title {
            font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
            color: #64748b; padding: 16px 18px 6px;
        }
        .nav-item-link {
            display: flex; align-items: center; gap: 10px; padding: 9px 16px; margin: 2px 8px;
            border-radius: 8px; color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500;
            transition: background 0.15s, color 0.15s;
        }
        .nav-item-link:hover { background-color: #1e293b; color: #ffffff; }
        .nav-item-link.active { background-color: var(--roam-accent); color: #ffffff; }
        .nav-item-link.active:hover { background-color: var(--roam-accent-strong); }
        .nav-item-link.is-disabled { opacity: 0.45; cursor: not-allowed; }
        .app-sidebar-user {
            height: 64px; min-height: 64px; background-color: #090d16;
            border-top: 1px solid var(--roam-sidebar-border);
            display: flex; align-items: center; gap: 10px; padding: 0 14px;
        }
        .app-main { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; overflow: hidden; }
        .app-topbar {
            height: 64px; min-height: 64px; background: #ffffff; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; gap: 12px; padding: 0 20px; flex-shrink: 0;
        }
        .app-content { flex: 1; overflow-y: auto; padding: 24px; }
        .app-footer { flex-shrink: 0; padding: 14px 24px; border-top: 1px solid #e2e8f0; background: #ffffff; color: #64748b; font-size: 12px; }
        .roam-backdrop { position: fixed; inset: 0; background: rgba(2, 6, 23, 0.5); z-index: 1030; }

        @media (max-width: 991.98px) {
            .app-sidebar { position: fixed; top: 0; left: 0; transform: translateX(-100%); }
            .app-sidebar.is-open { transform: translateX(0); }
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
<div class="app-shell" x-data="{ sidebarOpen: false }">
    @include('admin.partials.sidebar')

    <template x-if="sidebarOpen">
        <div class="roam-backdrop d-lg-none" @click="sidebarOpen = false"></div>
    </template>

    <div class="app-main">
        @include('admin.partials.navbar')

        <main class="app-content">
            <div class="container-fluid px-0">
                @include('admin.partials.flash')

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible mb-4 fade show" role="alert">
                        <div class="fw-bold mb-1">Please check the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        @include('admin.partials.footer')
    </div>
</div>

<!-- Tabler Core JS -->
<script src="{{ asset('tabler/dist/js/tabler.min.js') }}"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Global confirmation handler for forms with data-confirm.
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.matches('[data-confirm]') || form.dataset.confirmed) {
                return;
            }

            e.preventDefault();
            const message = form.getAttribute('data-confirm') || 'You will not be able to revert this action.';
            const title = form.getAttribute('data-confirm-title') || 'Are you sure?';
            const btnText = form.getAttribute('data-confirm-btn') || 'Yes, proceed';
            const icon = form.getAttribute('data-confirm-icon') || 'warning';

            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: btnText,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });
    });
</script>

@stack('scripts')
</body>
</html>
