<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Customer name is required',
            'customer_name.max' => 'Customer name cannot exceed 255 characters',
            'total_amount.required' => 'Total amount is required',
            'total_amount.numeric' => 'Total amount must be a valid number',
            'total_amount.min' => 'Total amount must be at least 0.01',
            'total_amount.max' => 'Total amount cannot exceed 999,999.99',
        ];
    }
}
