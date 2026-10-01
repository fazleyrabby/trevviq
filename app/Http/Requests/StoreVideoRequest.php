<?php

namespace App\Http\Requests;

use App\Enums\VideoVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $maxKb = (int) config('trevviq.video.max_size_mb') * 1024;
        $mimeTypes = (array) config('trevviq.video.mime_types');

        return [
            'video' => [
                'required',
                'file',
                'mimetypes:'.implode(',', $mimeTypes),
                'max:'.$maxKb,
            ],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['nullable', Rule::enum(VideoVisibility::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'video.mimetypes' => 'Videos must be MP4 or MOV files.',
            'video.max' => 'Videos may not be larger than '.config('trevviq.video.max_size_mb').' MB.',
        ];
    }
}
