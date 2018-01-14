<?php

namespace ShiftOneLabs\SingularResourceRoutes\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Foundation\Testing\TestCase;
use ShiftOneLabs\SingularResourceRoutes\SingularResourceRoutesServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function createApplication()
    {
        $app = new Application();

        $app->register(SingularResourceRoutesServiceProvider::class);

        return $app;
    }

    public function testResourceRegistrarRegistered()
    {
        $this->assertTrue($this->app->bound(ResourceRegistrar::class));
    }
}
