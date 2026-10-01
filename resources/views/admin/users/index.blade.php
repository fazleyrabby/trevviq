@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-subtitle', 'Travellers and community accounts')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ old('search', $search) }}"
                           class="form-control" placeholder="Name, username or email">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roles as $case)
                            <option value="{{ $case->value }}" @selected($role === $case->value)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Reviews</th>
                        <th>Videos</th>
                        <th>Places</th>
                        <th>Verified</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="text-secondary">{{ '@' . $user->username }}</td>
                            <td class="text-secondary">{{ $user->email }}</td>
                            <td>{{ $user->role->label() }}</td>
                            <td>{{ number_format($user->reviews_count) }}</td>
                            <td>{{ number_format($user->videos_count) }}</td>
                            <td>{{ number_format($user->visits_count) }}</td>
                            <td>
                                @if ($user->email_verified_at)
                                    <span class="badge bg-green-lt">Verified</span>
                                @else
                                    <span class="badge bg-secondary-lt">Unverified</span>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $user->created_at->format('M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
