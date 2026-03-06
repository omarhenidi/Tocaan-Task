<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $order = $this->route('order');

        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'order_number' => ['nullable', 'string', 'max:255', Rule::unique('orders', 'order_number')->ignore($order?->id)],
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'required', 'email'],
            'status' => ['nullable', 'string', Rule::in(OrderStatus::getValues())],
            'payment_status' => ['nullable', 'string', Rule::in(PaymentStatus::getValues())],
            'currency' => ['nullable', 'string', 'size:3'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
