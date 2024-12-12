<?php

namespace mindtwo\Appointable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LinkableDataScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return void
     *
     * @phpstan-ignore-next-line - Ignore the missing generic type for Builder
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($this->hasScope($builder, 'loadLinkable') && $this->isAppointableIndexRequest()) {
            // @phpstan-ignore-next-line - We know that the method exists
            $builder->loadLinkable();
        }
    }

    /**
     * Check if the builder has a given global scope.
     *
     * @phpstan-ignore-next-line - Ignore the missing generic type for Builder
     */
    protected function hasScope(Builder $builder, string $scopeName): bool
    {
        // Inspect the global scopes on the builder
        return $builder->hasNamedScope($scopeName) || method_exists($builder, $scopeName);
    }

    protected function isAppointableIndexRequest(): bool
    {
        return request()->routeIs('*.appointments.*');
    }
}
