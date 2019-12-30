<?php
namespace AppArk\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DeactivatedScope implements Scope
{
    protected $extensions = [
        'WithDeactivated',
        'OnlyDeactivated',
        'OnlyActivated',
        'Activate',
        'Deactivate'
    ];

    public function apply(Builder $builder, Model $model)
    {
        if (defined(get_class($model) . '::DEACTIVATE_USING') && $model::DEACTIVATE_USING) {
            $builder->whereNull($model->getQualifiedDeactivateAtColumn());
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Get the "deactivated_at" column for the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getDeletedAtColumn(Builder $builder)
    {
        /**
         * @var \Illuminate\Database\Eloquent\Model|Deactivates $model
         */
        $model = $builder->getModel();
        if (count($builder->getQuery()->joins) > 0) {
            return $model->getQualifiedDeactivateAtColumn();
        }

        return $model->getDeactivateAtColumn();
    }

    /**
     * Add the with-deactivated extension to the builder.
     *
     * @param Builder $builder
     */
    protected function addWithDeactivated(Builder $builder)
    {
        $builder->macro('withDeactivated', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the only-deactivated extension to the builder.
     *
     * @param Builder $builder
     */
    protected function addOnlyDeactivated(Builder $builder)
    {
        $builder->macro('onlyDeactivated', function (Builder $builder) {
            /**
             * @var \Illuminate\Database\Eloquent\Model|Deactivates $model
             */
            $model = $builder->getModel();

            $builder->whereNotNull(
                $model->getQualifiedDeactivateAtColumn()
            );

            return $builder;
        });
    }

    /**
     * Add the only-activated extension to the builder.
     *
     * @param Builder $builder
     */
    protected function addOnlyActivated(Builder $builder)
    {
        $builder->macro('onlyActivated', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->whereNull(
                $model->getQualifiedDeactivateAtColumn()
            );

            return $builder;
        });
    }

    /**
     * Add the activate extension to the builder.
     *
     * @param Builder $builder
     */
    protected function addActivate(Builder $builder)
    {
        $builder->macro('activate', function (Builder $builder) {
            $builder->withDeactivated();

            return $builder->update([$builder->getModel()->getDeactivateAtColumn() => null]);
        });
    }

    /**
     * Add the deactivate extension to the builder.
     *
     * @param Builder $builder
     */
    protected function addDeactivate(Builder $builder)
    {
        $builder->macro('deactivate', function (Builder $builder) {
            return $builder->update([$builder->getModel()->getDeactivateAtColumn() => $builder->getModel()->freshTimestampString()]);
        });
    }
}
