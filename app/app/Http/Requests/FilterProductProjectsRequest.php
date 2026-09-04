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
            'market' => ['nullable', 'string', 'max:16'],
            'priority' => ['nullable', Rule::in(['high', 'medium', 'low'])],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
