<?php

namespace ShiftOneLabs\SingularResourceRoutes;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class SingularResourceRoutesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BaseResourceRegistrar::class, ResourceRegistrar::class);
    }
}
