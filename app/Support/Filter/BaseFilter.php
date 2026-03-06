<?php

namespace App\Support\Filter;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    protected array $mappings = [];

    abstract public function filter(Builder $builder, $value): void;

    protected function caseInsensitiveLike(Builder $builder, string $column, $value)
    {
        return $builder->whereRaw('LOWER(' . $column . ') LIKE ?', ['%' . strtolower((string) $value) . '%']);
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }
}