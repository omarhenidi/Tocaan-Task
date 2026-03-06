<?php

namespace App\Support\Filter\Handler\Order;

use App\Support\Filter\BaseFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DateFilter extends BaseFilter
{
    public function filter(Builder $builder, $value): void
    {
        if ($value) {
            $builder->where('date', Carbon::parse($value)->format('Y-m-d'));
        }
    }
}
