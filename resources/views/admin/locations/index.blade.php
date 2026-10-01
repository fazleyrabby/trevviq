@extends('layouts.admin')

@section('title', 'Locations')
@section('page-title', 'Locations')
@section('page-subtitle', 'Geographic content engine')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.locations.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ old('search', $search) }}"
                           class="form-control" placeholder="Name or slug">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        @foreach ($types as $case)
                            <option value="{{ $case->value }}" @selected($type === $case->value)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Country</th>
                        <th>Content</th>
                        <th>Verified</th>
                        <th>Indexable</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <td>
                                <a href="{{ route('travel.show', $location->full_slug) }}" target="_blank" rel="noopener">
                                    {{ $location->name }}
                                </a>
                            </td>
                            <td>{{ $location->type->label() }}</td>
                            <td class="text-secondary">{{ $location->country_code ?? '—' }}</td>
                            <td>{{ number_format($location->content_count) }}</td>
                            <td>
                                @if ($location->is_verified)
                                    <span class="badge bg-green-lt">Verified</span>
                                @else
                                    <span class="badge bg-secondary-lt">Unverified</span>
                                @endif
                                <form action="{{ route('admin.locations.update', $location) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_verified" value="{{ $location->is_verified ? 0 : 1 }}">
                                    <button type="submit" class="btn btn-sm btn-ghost-secondary">{{ $location->is_verified ? 'Unverify' : 'Verify' }}</button>
                                </form>
                            </td>
                            <td>
                                @if ($location->indexable)
                                    <span class="badge bg-green-lt">Indexable</span>
                                @else
                                    <span class="badge bg-secondary-lt">Noindex</span>
                                @endif
                            </td>
                            <td>
                                @if ($location->is_active)
                                    <span class="badge bg-green-lt">Active</span>
                                @else
                                    <span class="badge bg-secondary-lt">Inactive</span>
                                @endif
                                <form action="{{ route('admin.locations.update', $location) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $location->is_active ? 0 : 1 }}">
                                    <button type="submit" class="btn btn-sm btn-ghost-secondary">{{ $location->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No locations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($locations->hasPages())
            <div class="card-footer">
                {{ $locations->links() }}
            </div>
        @endif
    </div>
@endsection
