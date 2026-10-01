@extends('layouts.admin')

@section('title', 'Reviews')
@section('page-title', 'Reviews')
@section('page-subtitle', 'Moderate traveller reviews')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Moderation queue</h3>
            <div class="ms-auto d-flex flex-wrap gap-1">
                <a href="{{ route('admin.reviews.index') }}"
                   class="btn btn-sm {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                @foreach ($statuses as $case)
                    <a href="{{ route('admin.reviews.index', ['status' => $case->value]) }}"
                       class="btn btn-sm {{ $status === $case->value ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $case->label() }}</a>
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Rating</th>
                        <th>Location</th>
                        <th>Excerpt</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="w-1">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $review->user?->name ?? 'Unknown' }}</td>
                            <td>
                                <span class="text-amber">
                                    <i data-lucide="star" style="width: 14px; height: 14px;"></i>
                                </span>
                                <span class="ms-1">{{ $review->rating }}/5</span>
                            </td>
                            <td>{{ $review->location?->name ?? '—' }}</td>
                            <td class="text-secondary">{{ Str::limit($review->body, 80) }}</td>
                            <td>
                                @if ($review->status === \App\Enums\ReviewStatus::Approved)
                                    <span class="badge bg-green-lt">{{ $review->status->label() }}</span>
                                @else
                                    <span class="badge bg-amber-lt">{{ $review->status->label() }}</span>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $review->created_at->diffForHumans() }}</td>
                            <td>
                                <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach ($statuses as $case)
                                            <option value="{{ $case->value }}" @selected($review->status === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No reviews found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->hasPages())
            <div class="card-footer">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@endsection
