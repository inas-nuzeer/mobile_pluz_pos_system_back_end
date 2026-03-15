<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait TenantTrait
{
    /**
     * The "booted" method of the model.
     */
    protected static function bootTenantTrait(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->shop_id = auth()->user()->shop_id;
            }
        });
    }
}
