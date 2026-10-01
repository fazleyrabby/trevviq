@php
    $isUpdate = $review !== null;
    $formKey = $isUpdate ? $review->id : 'new';
    $selectedRating = old('rating', $review?->rating);
    $selectedVisitDate = old('visit_date', $review?->visit_date?->format('Y-m-d'));
    $inputClasses = 'mt-1.5 w-full rounded-lg border bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500';
@endphp

<div class="rounded-xl border border-white/10 bg-white/5 p-5 sm:p-6">
    <h3 class="text-base font-semibold text-white">
        {{ $isUpdate ? 'Edit your review' : 'Share your experience' }}
    </h3>

    <form method="POST"
          action="{{ $isUpdate ? route('reviews.update', $review) : route('reviews.store') }}"
          class="mt-4 space-y-5">
        @csrf

        @if ($isUpdate)
            @method('PUT')
        @else
            <input type="hidden" name="location_id" value="{{ $location->id }}">
        @endif

        @error('location_id')
            <p class="text-sm text-rose-300">{{ $message }}</p>
        @enderror

        <div>
            <label for="review-rating-{{ $formKey }}" class="block text-sm font-medium text-slate-200">Rating</label>
            <select id="review-rating-{{ $formKey }}"
                    name="rating"
                    required
                    @error('rating') aria-invalid="true" aria-describedby="review-rating-error-{{ $formKey }}" @enderror
                    class="{{ $inputClasses }} {{ $errors->has('rating') ? 'border-rose-500/60' : 'border-white/10' }}">
                <option value="" disabled @selected(is_null($selectedRating))>Select a rating</option>
                @foreach ([5 => 'Excellent', 4 => 'Good', 3 => 'Average', 2 => 'Poor', 1 => 'Terrible'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) $selectedRating === (string) $value)>{{ $value }} &mdash; {{ $label }}</option>
                @endforeach
            </select>
            @error('rating')
                <p id="review-rating-error-{{ $formKey }}" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="review-title-{{ $formKey }}" class="block text-sm font-medium text-slate-200">
                Title <span class="font-normal text-slate-500">(optional)</span>
            </label>
            <input type="text"
                   id="review-title-{{ $formKey }}"
                   name="title"
                   maxlength="120"
                   value="{{ old('title', $review?->title) }}"
                   @error('title') aria-invalid="true" aria-describedby="review-title-error-{{ $formKey }}" @enderror
                   class="{{ $inputClasses }} {{ $errors->has('title') ? 'border-rose-500/60' : 'border-white/10' }}">
            @error('title')
                <p id="review-title-error-{{ $formKey }}" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="review-body-{{ $formKey }}" class="block text-sm font-medium text-slate-200">Your review</label>
            <textarea id="review-body-{{ $formKey }}"
                      name="body"
                      rows="5"
                      required
                      minlength="10"
                      maxlength="5000"
                      @error('body') aria-invalid="true" aria-describedby="review-body-error-{{ $formKey }}" @enderror
                      class="{{ $inputClasses }} {{ $errors->has('body') ? 'border-rose-500/60' : 'border-white/10' }}">{{ old('body', $review?->body) }}</textarea>
            @error('body')
                <p id="review-body-error-{{ $formKey }}" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="review-visit-date-{{ $formKey }}" class="block text-sm font-medium text-slate-200">
                Date of visit <span class="font-normal text-slate-500">(optional)</span>
            </label>
            <input type="date"
                   id="review-visit-date-{{ $formKey }}"
                   name="visit_date"
                   max="{{ now()->toDateString() }}"
                   value="{{ $selectedVisitDate }}"
                   @error('visit_date') aria-invalid="true" aria-describedby="review-visit-date-error-{{ $formKey }}" @enderror
                   class="{{ $inputClasses }} [color-scheme:dark] {{ $errors->has('visit_date') ? 'border-rose-500/60' : 'border-white/10' }}">
            @error('visit_date')
                <p id="review-visit-date-error-{{ $formKey }}" class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
            {{ $isUpdate ? 'Save changes' : 'Publish review' }}
        </button>
    </form>
</div>
