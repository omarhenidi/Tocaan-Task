<?php

namespace App\Support\Filter\Handler\Order;

use App\Support\Filter\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter extends BaseFilter
{
    public function filter(Builder $builder, $value): void
    {
        if ($value) {
            $builder->where('status', $value);
        }
    }
}
