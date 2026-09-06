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
            'category' => ['required', 'string', 'max:100'],
            'market' => ['nullable', Rule::in(['US'])],
            'priority' => ['required', Rule::in(['initial_screening', 'market_new', 'historical_winner'])],
            'product_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
