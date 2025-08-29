<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\PricingService;

class SaleRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $pricingService = new PricingService();
        $availableSizes = implode(',', $pricingService->getAvailableSizes());
        
        return [
            'user_id' => 'required|exists:users,id',
            'base_price_11_8' => 'required|numeric|min:0|max:999999.99',
            'discount_amount' => 'nullable|numeric|min:0|max:999999.99',
            'items' => 'required|array|min:1',
            'items.*.size_kg' => "required|numeric|in:{$availableSizes}",
            'items.*.quantity' => 'required|integer|min:0|max:999',
            'items.*.unit_price' => 'nullable|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Customer is required.',
            'user_id.exists' => 'Selected customer does not exist.',
            'base_price_11_8.required' => 'Base price for 11.8kg cylinder is required.',
            'base_price_11_8.numeric' => 'Base price must be a valid number.',
            'base_price_11_8.min' => 'Base price cannot be negative.',
            'base_price_11_8.max' => 'Base price is too high.',
            'discount_amount.numeric' => 'Discount amount must be a valid number.',
            'discount_amount.min' => 'Discount amount cannot be negative.',
            'discount_amount.max' => 'Discount amount is too high.',
            'items.required' => 'At least one item is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.size_kg.required' => 'Cylinder size is required.',
            'items.*.size_kg.numeric' => 'Cylinder size must be a valid number.',
            'items.*.size_kg.in' => 'Invalid cylinder size. Available sizes: 6.0, 11.8, 15.0, 45.4 kg.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity is too high.',
            'items.*.unit_price.numeric' => 'Unit price must be a valid number.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'items.*.unit_price.max' => 'Unit price is too high.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateItems($validator);
            $this->validateDiscount($validator);
        });
    }

    /**
     * Validate items array
     */
    private function validateItems($validator): void
    {
        $items = $this->input('items', []);
        $hasValidItems = false;

        foreach ($items as $index => $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $hasValidItems = true;
                break;
            }
        }

        if (!$hasValidItems) {
            $validator->errors()->add('items', 'At least one item must have a quantity greater than 0.');
        }
    }

    /**
     * Validate discount amount
     */
    private function validateDiscount($validator): void
    {
        $items = $this->input('items', []);
        $discountAmount = $this->input('discount_amount', 0);
        
        // Calculate subtotal
        $subTotal = 0;
        foreach ($items as $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $unitPrice = $item['unit_price'] ?? 0;
                $subTotal += $item['quantity'] * $unitPrice;
            }
        }

        if ($discountAmount > $subTotal) {
            $validator->errors()->add('discount_amount', 'Discount amount cannot exceed subtotal.');
        }
    }
}
