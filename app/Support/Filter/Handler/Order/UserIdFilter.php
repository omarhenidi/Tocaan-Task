<?php

namespace App\Support\Filter\Handler\Order;

use App\Support\Filter\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class UserIdFilter extends BaseFilter
{
    public function filter(Builder $builder, $value): void
    {
        if ($value !== null && $value !== '') {
            $builder->where('user_id', (int) $value);
        }
    }
}
