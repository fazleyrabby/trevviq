@php
    $distanceMeters = $distance ?? ($location->distance_meters ?? null);

    if (! is_null($distanceMeters)) {
        $distanceLabel = $distanceMeters < 1000
            ? round($distanceMeters).' m'
            : number_format($distanceMeters / 1000, 1).' km';
    }
@endphp

<a href="{{ route('travel.show', $location->full_slug) }}"
   class="group flex items-start justify-between gap-4 rounded-xl border border-white/10 bg-white/5 p-5 transition hover:border-teal-500/40 hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
    <span class="min-w-0">
        <span class="block truncate font-semibold text-white group-hover:text-teal-200">{{ $location->name }}</span>
        <span class="mt-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
            {{ $location->type->label() }}@if ($location->country_code) &middot; {{ $location->country_code }}@endif
        </span>
    </span>

    @isset($distanceLabel)
        <span class="shrink-0 rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-xs font-medium text-teal-300">
            {{ $distanceLabel }}
        </span>
    @endisset
</a>
