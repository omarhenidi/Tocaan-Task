<?php

namespace App\Support\Filter\Handler\Order;

use App\Support\Filter\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class PaymentStatusFilter extends BaseFilter
{
    public function filter(Builder $builder, $value): void
    {
        if ($value) {
            if (is_array($value)) {
                $builder->whereIn('payment_status', $value);
            } else {
                $builder->where('payment_status', $value);
            }
        }
    }
}
