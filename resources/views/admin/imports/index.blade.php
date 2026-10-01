@extends('layouts.admin')

@section('title', 'Import status')
@section('page-title', 'Import status')
@section('page-subtitle', 'Geographic import batches and coverage')

@section('content')
    @php
        $cards = [
            ['label' => 'Locations', 'value' => $stats['locations'], 'icon' => 'map-pin', 'color' => 'teal'],
            ['label' => 'Indexable', 'value' => $stats['indexable'], 'icon' => 'search', 'color' => 'green'],
            ['label' => 'Countries', 'value' => $stats['countries'], 'icon' => 'globe', 'color' => 'blue'],
            ['label' => 'Cities', 'value' => $stats['cities'], 'icon' => 'building-2', 'color' => 'amber'],
        ];
    @endphp

    <div class="row row-deck row-cards mb-3">
        @foreach ($cards as $card)
            <div class="col-6 col-lg-3">
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import batches</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Countries</th>
                        <th>Total</th>
                        <th>Processed</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Skipped</th>
                        <th>Started</th>
                        <th>Finished</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td>{{ $batch->source }}</td>
                            <td>
                                @switch($batch->status)
                                    @case('completed')
                                        <span class="badge bg-green-lt">{{ ucfirst($batch->status) }}</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-red-lt">{{ ucfirst($batch->status) }}</span>
                                        @break
                                    @case('running')
                                        <span class="badge bg-amber-lt">{{ ucfirst($batch->status) }}</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary-lt">{{ ucfirst($batch->status) }}</span>
                                @endswitch
                            </td>
                            <td class="text-secondary">{{ !empty($batch->countries) ? implode(', ', $batch->countries) : 'all' }}</td>
                            <td>{{ number_format($batch->total) }}</td>
                            <td>{{ number_format($batch->processed) }}</td>
                            <td>{{ number_format($batch->created) }}</td>
                            <td>{{ number_format($batch->updated) }}</td>
                            <td>{{ number_format($batch->skipped) }}</td>
                            <td class="text-secondary">{{ $batch->started_at?->format('M j, H:i') ?? '—' }}</td>
                            <td class="text-secondary">{{ $batch->finished_at?->format('M j, H:i') ?? '—' }}</td>
                            <td class="text-secondary">
                                @if ($batch->error)
                                    {{ Str::limit($batch->error, 60) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-secondary py-4">No import batches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($batches->hasPages())
            <div class="card-footer">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
@endsection
