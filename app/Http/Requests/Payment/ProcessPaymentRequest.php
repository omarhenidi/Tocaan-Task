<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_id' => ['nullable', 'string', 'max:255'],
            'payment_status' => ['nullable', 'string', Rule::in(PaymentStatus::getValues())],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::getValues())],
            'simulate_success' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID is required.',
            'order_id.exists' => 'Order not found.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method. Use credit_card or paypal.',
        ];
    }
}
