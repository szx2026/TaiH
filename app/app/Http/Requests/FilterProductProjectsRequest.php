<?php

namespace App\Http\Requests;

use App\Support\ProjectStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterProductProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage' => ['nullable', Rule::in(ProjectStage::ordered())],
            'status' => ['nullable', Rule::in(['draft', 'in_progress', 'blocked', 'approved', 'rejected', 'archived'])],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', Rule::in(['initial_screening', 'market_new', 'historical_winner', 'high', 'medium', 'low'])],
            'search' => ['nullable', 'string', 'max:100'],
            'project' => ['nullable', 'integer', Rule::exists('product_projects', 'id')],
        ];
    }
}
