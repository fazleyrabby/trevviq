@extends('layouts.admin')

@section('title', 'Videos')
@section('page-title', 'Videos')
@section('page-subtitle', 'Moderate traveller videos')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Moderation queue</h3>
            <div class="ms-auto d-flex flex-wrap gap-1">
                <a href="{{ route('admin.videos.index') }}"
                   class="btn btn-sm {{ $status === null ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                @foreach ($statuses as $case)
                    <a href="{{ route('admin.videos.index', ['status' => $case->value]) }}"
                       class="btn btn-sm {{ $status === $case->value ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $case->label() }}</a>
                @endforeach
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Location</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="w-1">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($videos as $video)
                        <tr>
                            <td>
                                @if ($video->thumbnailUrl())
                                    <img src="{{ $video->thumbnailUrl() }}" alt="" class="rounded"
                                         style="width: 56px; height: 36px; object-fit: cover;">
                                @else
                                    <span class="avatar rounded bg-secondary-lt">
                                        <i data-lucide="video" style="width: 16px; height: 16px;"></i>
                                    </span>
                                @endif
                            </td>
                            <td>{{ $video->title ?: 'Untitled' }}</td>
                            <td>{{ $video->user?->name ?? 'Unknown' }}</td>
                            <td>{{ $video->location?->name ?? '—' }}</td>
                            <td class="text-secondary">{{ $video->duration ?? 0 }}s</td>
                            <td>
                                @switch($video->status->value)
                                    @case('published')
                                        <span class="badge bg-green-lt">{{ $video->status->label() }}</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-amber-lt">{{ $video->status->label() }}</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-blue-lt">{{ $video->status->label() }}</span>
                                        @break
                                    @case('rejected')
                                    @case('blocked')
                                        <span class="badge bg-red-lt">{{ $video->status->label() }}</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary-lt">{{ $video->status->label() }}</span>
                                @endswitch
                            </td>
                            <td class="text-secondary">{{ $video->created_at->diffForHumans() }}</td>
                            <td>
                                <form action="{{ route('admin.videos.update', $video) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach ($statuses as $case)
                                            <option value="{{ $case->value }}" @selected($video->status === $case)>{{ $case->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">No videos found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($videos->hasPages())
            <div class="card-footer">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
@endsection
