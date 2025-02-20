<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NYTBestSellersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users to access this endpoint
    }

    public function rules(): array
    {
        return [
            'author' => 'nullable|string',
            'isbn' => 'nullable|array',
            'isbn.*' => 'string|size:10', // Ensure each ISBN is a valid 10-character string
            'title' => 'nullable|string',
            'offset' => 'nullable|integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.*.size' => 'Each ISBN must be exactly 10 characters long.',
            'offset.min' => 'Offset must be a non-negative number.'
        ];
    }
}
