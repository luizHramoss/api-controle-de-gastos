<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'min:2', 'max:255'],
            'amount'      => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'date'        => ['required', 'date', 'before_or_equal:' . now()->addDay()->toDateString()],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', $this->user()->id)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required'  => 'The description field is required.',
            'description.min'       => 'The description must be at least 2 characters.',
            'amount.required'       => 'The amount field is required.',
            'amount.numeric'        => 'The amount must be a valid number.',
            'amount.gt'             => 'The amount must be greater than zero.',
            'date.required'         => 'The date field is required.',
            'date.date'             => 'Please provide a valid date.',
            'date.before_or_equal'  => 'The expense date cannot be more than 1 day in the future.',
            'category_id.required'  => 'Please select a category.',
            'category_id.exists'    => 'The selected category does not exist or does not belong to you.',
        ];
    }
}
