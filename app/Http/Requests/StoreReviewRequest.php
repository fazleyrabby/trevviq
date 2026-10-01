<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'visit_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Enforce the "one review per location per cooldown window" rule (Section 81).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $days = (int) config('trevviq.reviews.cooldown_days');
            $alreadyReviewed = Review::query()
                ->where('user_id', $this->user()->id)
                ->where('location_id', $this->integer('location_id'))
                ->where('created_at', '>=', now()->subDays($days))
                ->exists();

            if ($alreadyReviewed) {
                $validator->errors()->add(
                    'location_id',
                    "You have already reviewed this place within the last {$days} days.",
                );
            }
        });
    }
}
