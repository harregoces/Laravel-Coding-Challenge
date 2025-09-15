<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FavoriteDeleteRequest (TEMPLATE)
 * Implement validation for DELETE via API:
 *  - Either provide 'unique_hash' (size 64)
 *  - OR provide 'text' (+ optional 'author'), from which we derive the hash
 * Enforce "one of" semantics; do not allow both at once.
 */
final class FavoriteDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // TODO (candidate): enforce XOR semantics.
        // Example approach using built-in rules:
        // - unique_hash required_without:text and prohibited_with:text
        // - text required_without:unique_hash and prohibited_with:unique_hash
        return [
            'unique_hash' => ['nullable', 'string', 'size:64', 'required_without:text', 'prohibited_with:text'],
            'text'        => ['nullable', 'string', 'min:1', 'max:2000', 'required_without:unique_hash', 'prohibited_with:unique_hash'],
            'author'      => ['nullable', 'string', 'max:255'],
        ];
    }

    /** Helper for controller logic. */
    public function wantsHash(): bool
    {
        return filled($this->input('unique_hash'));
    }
}
