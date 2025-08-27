<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'vehicle_number' => ['required', 'string', 'max:50'],
            'weight_ton' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'rate_11_8_kg' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a supplier.',
            'user_id.exists' => 'The selected supplier is invalid.',
            'vehicle_number.required' => 'Vehicle number is required.',
            'vehicle_number.max' => 'Vehicle number cannot exceed 50 characters.',
            'weight_ton.required' => 'Weight in ton is required.',
            'weight_ton.numeric' => 'Weight must be a valid number.',
            'weight_ton.min' => 'Weight must be at least 0.01 ton.',
            'weight_ton.max' => 'Weight cannot exceed 1000 ton.',
            'rate_11_8_kg.required' => 'Rate per 11.8kg cylinder is required.',
            'rate_11_8_kg.numeric' => 'Rate must be a valid number.',
            'rate_11_8_kg.min' => 'Rate must be at least 0.01.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'weight_ton' => (float) $this->weight_ton,
            'rate_11_8_kg' => (float) $this->rate_11_8_kg,
        ]);
    }
}

