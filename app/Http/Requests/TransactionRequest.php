<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Transaction;

class TransactionRequest extends FormRequest
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
        $rules = [
            // 'user_id' => 'required|exists:users,id',
            // 'type' => 'required|in:' . implode(',', Transaction::getTypes()),
            'amount' => 'required|numeric|min:-999999.99|max:999999.99',
            'method' => 'required|exists:payment_methods,id'
            // 'note' => 'nullable|string|max:1000',
        ];

        // Add sale_id validation if provided
        if ($this->input('sale_id')) {
            $rules['sale_id'] = 'exists:sales,id';
        }

        // Add payment method validation for payment transactions
        if ($this->input('type') === Transaction::TYPE_PAYMENT_IN) {
            $rules['method'] = 'required|in:' . implode(',', Transaction::getPaymentMethods());
            $rules['reference'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Customer is required.',
            'user_id.exists' => 'Selected customer does not exist.',
            'type.required' => 'Transaction type is required.',
            'type.in' => 'Invalid transaction type.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount is too low.',
            'amount.max' => 'Amount is too high.',
            'sale_id.exists' => 'Selected sale does not exist.',
            'method.required' => 'Payment method is required for payment transactions.',
            'method.in' => 'Invalid payment method.',
            'reference.string' => 'Reference must be a string.',
            'reference.max' => 'Reference is too long.',
            'note.string' => 'Note must be a string.',
            'note.max' => 'Note is too long.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateAmount($validator);
            $this->validatePaymentAmount($validator);
        });
    }

    /**
     * Validate amount based on transaction type
     */
    private function validateAmount($validator): void
    {
        $type = $this->input('type');
        $amount = $this->input('amount');

        if ($type === Transaction::TYPE_SALE && $amount <= 0) {
            $validator->errors()->add('amount', 'Sale amount must be positive.');
        }

        if (in_array($type, [Transaction::TYPE_PAYMENT_IN, Transaction::TYPE_REFUND]) && $amount >= 0) {
            $validator->errors()->add('amount', ucfirst($type) . ' amount must be negative.');
        }
    }

    /**
     * Validate payment amount against sale balance
     */
    private function validatePaymentAmount($validator): void
    {
        $type = $this->input('type');
        $saleId = $this->input('sale_id');
        $amount = $this->input('amount');

        if ($type === Transaction::TYPE_PAYMENT && $saleId) {
            $sale = \App\Models\Sale::find($saleId);
            if ($sale) {
                $balance = $sale->balance;
                $paymentAmount = abs($amount);

                if ($paymentAmount > $balance) {
                    $validator->errors()->add('amount', 'Payment amount cannot exceed the outstanding balance of PKR' . number_format($balance, 2) . '.');
                }
            }
        }
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure amount is numeric
        if ($this->has('amount')) {
            $this->merge([
                'amount' => (float) $this->input('amount'),
            ]);
        }
    }
}
