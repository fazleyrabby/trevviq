@if ($breadcrumbs->isNotEmpty())
    <nav aria-label="Breadcrumb" class="text-sm">
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-slate-400">
            <li>
                <a href="{{ route('travel.index') }}"
                   class="rounded px-0.5 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Explore
                </a>
            </li>
            @foreach ($breadcrumbs as $crumb)
                <li class="flex items-center gap-x-1.5">
                    <span aria-hidden="true" class="text-slate-600">/</span>
                    @if ($loop->last)
                        <span aria-current="page" class="font-medium text-slate-200">{{ $crumb->name }}</span>
                    @else
                        <a href="{{ route('travel.show', $crumb->full_slug) }}"
                           class="rounded px-0.5 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                            {{ $crumb->name }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
