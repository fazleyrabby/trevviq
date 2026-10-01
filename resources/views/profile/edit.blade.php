@extends('layouts.frontend')

@section('title', 'Edit profile — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-2xl px-6 py-12 sm:py-16">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-white">Edit profile</h1>
            <p class="mt-2 text-sm text-slate-400">Keep your public profile up to date.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-4">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" class="h-16 w-16 rounded-full object-cover"/>
                    @else
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-teal-500 text-xl font-bold text-white">
                            {{ $user->initials() }}
                        </span>
                    @endif
                    <div class="flex-1">
                        <label for="avatar" class="mb-1.5 block text-sm font-medium text-slate-300">Avatar</label>
                        <input id="avatar"
                               name="avatar"
                               type="file"
                               accept="image/png,image/jpeg,image/webp"
                               aria-invalid="{{ $errors->has('avatar') ? 'true' : 'false' }}"
                               @error('avatar') aria-describedby="avatar-error" @enderror
                               class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 file:mr-3 file:rounded-md file:border-0 file:bg-teal-500 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-400 @error('avatar') border-red-500/50 @enderror"/>
                        <p class="mt-1 text-xs text-slate-500">PNG, JPG, or WebP.</p>
                        @error('avatar')
                            <p id="avatar-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Full name</label>
                    <input id="name"
                           name="name"
                           type="text"
                           value="{{ old('name', $user->name) }}"
                           autocomplete="name"
                           required
                           aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                           @error('name') aria-describedby="name-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-red-500/50 @enderror"/>
                    @error('name')
                        <p id="name-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-300">Username</label>
                    <input id="username"
                           name="username"
                           type="text"
                           value="{{ old('username', $user->username) }}"
                           autocomplete="off"
                           required
                           aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                           @error('username') aria-describedby="username-error" @enderror
                           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('username') border-red-500/50 @enderror"/>
                    @error('username')
                        <p id="username-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="mb-1.5 block text-sm font-medium text-slate-300">Bio</label>
                    <textarea id="bio"
                              name="bio"
                              rows="4"
                              aria-invalid="{{ $errors->has('bio') ? 'true' : 'false' }}"
                              @error('bio') aria-describedby="bio-error" @enderror
                              class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('bio') border-red-500/50 @enderror"
                              placeholder="Tell people a little about yourself and the places you love.">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p id="bio-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="country_code" class="mb-1.5 block text-sm font-medium text-slate-300">Country code</label>
                    <input id="country_code"
                           name="country_code"
                           type="text"
                           value="{{ old('country_code', $user->country_code) }}"
                           maxlength="2"
                           autocomplete="country"
                           aria-invalid="{{ $errors->has('country_code') ? 'true' : 'false' }}"
                           @error('country_code') aria-describedby="country_code-error" @enderror
                           class="w-full max-w-[8rem] rounded-lg border border-white/10 bg-white/5 px-3 py-2 uppercase text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('country_code') border-red-500/50 @enderror"
                           placeholder="US"/>
                    @error('country_code')
                        <p id="country_code-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('profile.show') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/5">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
