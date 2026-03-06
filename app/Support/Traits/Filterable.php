<?php

namespace App\Support\Traits;

use App\Support\Filter\FiltrationEngine;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $builder, array $filterRequest, array $filters = []): Builder
    {
        (new FiltrationEngine($builder, $filterRequest))->plugFilters($filters)->run();

        return $builder;
    }
}
