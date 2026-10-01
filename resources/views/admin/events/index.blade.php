@extends('layouts.admin')

@section('title', 'Events')
@section('page-title', 'Events')
@section('page-subtitle', 'Moderate traveller events')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Moderation queue</h3>
            <div class="ms-auto d-flex flex-wrap gap-1">
                <a href="{{ route('admin.events.index') }}"
                   class="btn btn-sm {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                @foreach ($statuses as $case)
                    <a href="{{ route('admin.events.index', ['status' => $case->value]) }}"
                       class="btn btn-sm {{ $status === $case->value ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $case->label() }}</a>
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Organizer</th>
                        <th>Location</th>
                        <th>Starts</th>
                        <th>Status</th>
                        <th class="w-1">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>{{ $event->name }}</td>
                            <td>{{ $event->organizer?->name ?? 'Unknown' }}</td>
                            <td>{{ $event->location?->name ?? '—' }}</td>
                            <td class="text-secondary">{{ $event->starts_at->format('D, j M Y · g:ia') }}</td>
                            <td>
                                @switch($event->status->value)
                                    @case('published')
                                        <span class="badge bg-green-lt">{{ $event->status->label() }}</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-amber-lt">{{ $event->status->label() }}</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-blue-lt">{{ $event->status->label() }}</span>
                                        @break
                                    @case('rejected')
                                    @case('blocked')
                                        <span class="badge bg-red-lt">{{ $event->status->label() }}</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary-lt">{{ $event->status->label() }}</span>
                                @endswitch
                            </td>
                            <td>
                                <form action="{{ route('admin.events.update', $event) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach ($statuses as $case)
                                            <option value="{{ $case->value }}" @selected($event->status === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No events found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($events->hasPages())
            <div class="card-footer">
                {{ $events->links() }}
            </div>
        @endif
    </div>
@endsection
