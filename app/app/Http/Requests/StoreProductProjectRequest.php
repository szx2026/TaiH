<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('administrator')
            || $this->user()?->department?->code === 'market_research';
    }

    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'market' => ['required', 'string', 'max:16'],
            'priority' => ['required', Rule::in(['high', 'medium', 'low'])],
        ];
    }
}
