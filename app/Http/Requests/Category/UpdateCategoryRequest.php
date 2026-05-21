<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories')
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id))
                    ->ignore($this->route('category')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.min'      => 'The category name must be at least 2 characters.',
            'name.max'      => 'The category name must not exceed 100 characters.',
            'name.unique'   => 'You already have a category with this name.',
        ];
    }
}
