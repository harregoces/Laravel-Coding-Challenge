<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FavoriteStoreRequest (TEMPLATE)
 * Implement server-side validation for adding a favorite.
 * Requirements (see CHALLENGE.md):
 *  - 'text' is required (string)
 *  - 'author' is optional (string)
 *  - Must be authenticated (web or sanctum)
 */
final class FavoriteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is behind auth, but be explicit:
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'text'   => ['required', 'string', 'min:1', 'max:2000'],
            'author' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'text' => is_string($this->text) ? trim($this->text) : $this->text,
            'author' => is_string($this->author) ? trim($this->author) : $this->author,
        ]);
    }
}
