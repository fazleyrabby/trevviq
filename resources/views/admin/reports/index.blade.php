@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Review flagged content')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Report queue</h3>
            <div class="ms-auto d-flex flex-wrap gap-1">
                <a href="{{ route('admin.reports.index') }}"
                   class="btn btn-sm {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                @foreach ($statuses as $case)
                    <a href="{{ route('admin.reports.index', ['status' => $case->value]) }}"
                       class="btn btn-sm {{ $status === $case->value ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $case->label() }}</a>
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Reporter</th>
                        <th>Target</th>
                        <th>Reason</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="w-1">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>{{ $report->reporter?->name ?? 'Guest' }}</td>
                            <td>
                                <span class="badge bg-blue-lt">{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}</span>
                                @if ($report->reportable instanceof \App\Models\Review)
                                    <div class="text-secondary small mt-1">{{ Str::limit($report->reportable->body, 50) }}</div>
                                @elseif ($report->reportable instanceof \App\Models\Video)
                                    <div class="text-secondary small mt-1">{{ $report->reportable->title ?: 'Untitled' }}</div>
                                @endif
                            </td>
                            <td>{{ $report->reason->label() }}</td>
                            <td class="text-secondary">{{ Str::limit($report->description, 80) }}</td>
                            <td>
                                @switch($report->status->value)
                                    @case('open')
                                        <span class="badge bg-amber-lt">{{ $report->status->label() }}</span>
                                        @break
                                    @case('reviewed')
                                        <span class="badge bg-green-lt">{{ $report->status->label() }}</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary-lt">{{ $report->status->label() }}</span>
                                @endswitch
                            </td>
                            <td class="text-secondary">{{ $report->created_at->diffForHumans() }}</td>
                            <td>
                                <form action="{{ route('admin.reports.update', $report) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach ($statuses as $case)
                                            <option value="{{ $case->value }}" @selected($report->status === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reports->hasPages())
            <div class="card-footer">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection
