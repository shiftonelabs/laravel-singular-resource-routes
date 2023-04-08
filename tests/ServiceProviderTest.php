<?php

namespace ShiftOneLabs\SingularResourceRoutes\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Foundation\Testing\TestCase;
use ShiftOneLabs\SingularResourceRoutes\SingularResourceRoutesServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function createApplication()
    {
        $app = new Application();

        // Facade root needed for PHP 7.3-7.4 / Laravel 8.x
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);

        $app->register(SingularResourceRoutesServiceProvider::class);

        return $app;
    }

    public function testResourceRegistrarRegistered()
    {
        $this->assertTrue($this->app->bound(ResourceRegistrar::class));
    }
}
