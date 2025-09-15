<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FavoriteDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // Either unique_hash OR text (+ optional author)
        return [
            'unique_hash' => ['nullable', 'string', 'size:64'],
            'text'        => ['nullable', 'string', 'min:1', 'max:2000'],
            'author'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function wantsHash(): bool
    {
        return filled($this->input('unique_hash'));
    }
}
