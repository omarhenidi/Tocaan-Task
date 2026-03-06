<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_id' => $this->payment_id,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'amount' => (float) $this->amount,
            'gateway_response' => $this->gateway_response,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
