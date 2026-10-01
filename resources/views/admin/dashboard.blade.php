@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Platform overview')

@section('content')
    @php
        $cards = [
            ['label' => 'Locations', 'value' => $stats['locations'], 'icon' => 'map-pin', 'color' => 'teal'],
            ['label' => 'Videos', 'value' => $stats['videos'], 'icon' => 'video', 'color' => 'blue'],
            ['label' => 'Reviews', 'value' => $stats['reviews'], 'icon' => 'star', 'color' => 'amber'],
            ['label' => 'Events', 'value' => $stats['events'], 'icon' => 'calendar-days', 'color' => 'purple'],
            ['label' => 'Travellers', 'value' => $stats['travellers'], 'icon' => 'users', 'color' => 'cyan'],
            ['label' => 'Pending moderation', 'value' => $stats['pending_moderation'], 'icon' => 'shield-alert', 'color' => 'red'],
        ];
    @endphp

    <div class="row row-deck row-cards">
        @foreach ($cards as $card)
            <div class="col-6 col-lg-4 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary small fw-medium">{{ $card['label'] }}</span>
                            <i data-lucide="{{ $card['icon'] }}" class="text-{{ $card['color'] }}" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div class="h1 mb-0">{{ number_format($card['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row row-deck row-cards mt-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent activity</h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i data-lucide="activity" class="text-secondary mb-3" style="width: 34px; height: 34px;"></i>
                        <p class="text-secondary mb-0">No activity yet. Traveller uploads, reviews, and events will appear here.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Getting started</h3>
                </div>
                <div class="list-group list-group-flush">
                    @php
                        $checklist = [
                            ['label' => 'Project foundation', 'done' => true],
                            ['label' => 'Authentication', 'done' => true],
                            ['label' => 'Location engine & import', 'done' => true],
                            ['label' => 'Public discovery & search', 'done' => true],
                            ['label' => 'Traveller profiles & travel history', 'done' => true],
                            ['label' => 'Video upload & processing', 'done' => true],
                            ['label' => 'Reviews & moderation', 'done' => true],
                            ['label' => 'Events', 'done' => false],
                            ['label' => 'Notifications', 'done' => false],
                        ];
                    @endphp

                    @foreach ($checklist as $item)
                        <div class="list-group-item d-flex align-items-center gap-2">
                            <i data-lucide="{{ $item['done'] ? 'check-circle-2' : 'circle' }}"
                               class="{{ $item['done'] ? 'text-teal' : 'text-secondary' }}"
                               style="width: 17px; height: 17px;"></i>
                            <span class="{{ $item['done'] ? 'text-decoration-line-through text-secondary' : '' }}">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer text-secondary small">
                    {{ $adminsCount }} administrator{{ $adminsCount === 1 ? '' : 's' }} configured.
                </div>
            </div>
        </div>
    </div>
@endsection
