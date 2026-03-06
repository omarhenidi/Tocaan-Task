<?php

namespace App\Support\Filter;

use App\Support\Filter\BaseFilter;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class FiltrationEngine
{
    protected array $requestFilters;

    protected Builder $builder;

    protected array $filters = [];

    public function __construct(Builder $builder, array $requestFilters)
    {
        $this->builder = $builder;
        $this->requestFilters = $requestFilters;
    }

    public function plugFilters(array $filters = []): self
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    public function run(): void
    {
        foreach ($this->relevantFilters() as $filterName => $value) {
            $filter = $this->resolveFilter($filterName);
            $value =  !is_array($value) ? $filter->getMappings()[$value] ?? $value : $value;
            $filter->filter($this->builder, $value);
        }
    }

    public function resolveFilter(string $filter): BaseFilter
    {
        if (!isset($this->filters[$filter])) {
            throw new Exception("Could not resolve filter associated with name: '{$filter}'");
        }

        return new $this->filters[$filter]();
    }

    protected function relevantFilters(): array
    {
        return Arr::only($this->requestFilters, array_keys($this->filters));
    }
}
