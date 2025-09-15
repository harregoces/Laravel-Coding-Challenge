<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FavoriteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // web: via auth middleware; api: sanctum
    }

    public function rules(): array
    {
        return [
            'text'   => ['required', 'string', 'min:1', 'max:2000'],
            'author' => ['nullable', 'string', 'max:255'],
        ];
    }
}
