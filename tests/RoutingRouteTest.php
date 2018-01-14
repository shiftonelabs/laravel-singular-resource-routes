<?php

namespace ShiftOneLabs\SingularResourceRoutes\Tests;

use Illuminate\Routing\Router;
use PHPUnit\Framework\TestCase;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Contracts\Routing\Registrar;

class RoutingRouteTest extends TestCase
{
    // Tests copied from illuminate/routing package. These tests are here
    // to make sure the normal resource routing still works. They are
    // slightly modified to work with 5.0 on up.
    public function testResourceRouting()
    {
        // Previous to 5.3, parameters were plural. In 5.3+, they are defaulted
        // to singular. Determine if the parameters will be singular or if
        // they will be plural for the tests.
        $singular = false;
        if (method_exists(ResourceRegistrar::class, 'singularParameters')) {
            ResourceRegistrar::singularParameters();
            $singular = true;
        }

        $router = $this->getRouter();
        $router->resource('foo', 'FooController');
        $routes = $router->getRoutes();
        $this->assertCount(7, $routes);

        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['only' => ['update']]);
        $routes = $router->getRoutes();

        $this->assertCount(1, $routes);

        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['only' => ['show', 'destroy']]);
        $routes = $router->getRoutes();

        $this->assertCount(2, $routes);

        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['except' => ['show', 'destroy']]);
        $routes = $router->getRoutes();

        $this->assertCount(5, $routes);

        $router = $this->getRouter();
        $router->resource('foo-bars', 'FooController', ['only' => ['show']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        if ($singular) {
            $this->assertEquals('foo-bars/{foo_bar}', $routes[0]->uri());
        } else {
            $this->assertEquals('foo-bars/{foo_bars}', $routes[0]->uri());
        }

        $router = $this->getRouter();
        $router->resource('foo-bar.foo-baz', 'FooController', ['only' => ['show']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo-bar/{foo_bar}/foo-baz/{foo_baz}', $routes[0]->uri());

        $router = $this->getRouter();
        $router->resource('foo-bars', 'FooController', ['only' => ['show'], 'as' => 'prefix']);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        if ($singular) {
            $this->assertEquals('foo-bars/{foo_bar}', $routes[0]->uri());
        } else {
            $this->assertEquals('foo-bars/{foo_bars}', $routes[0]->uri());
        }
        $this->assertEquals('prefix.foo-bars.show', $routes[0]->getName());

        // verbs property does not exist until 5.3.27
        if (method_exists(ResourceRegistrar::class, 'verbs')) {
            ResourceRegistrar::verbs([
                'create' => 'ajouter',
                'edit' => 'modifier',
            ]);

            $router = $this->getRouter();
            $router->resource('foo', 'FooController');
            $routes = $router->getRoutes();

            $this->assertEquals('foo/ajouter', $routes->getByName('foo.create')->uri());
            $this->assertEquals('foo/{foo}/modifier', $routes->getByName('foo.edit')->uri());

            // Reset the verbs back to normal.
            ResourceRegistrar::verbs([
                'create' => 'create',
                'edit' => 'edit',
            ]);
        }
    }

    // New tests for singular resource routing.
    public function testSingularResourceRouting()
    {
        // Test singular boolean specification creates only 6 singular routes.
        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['singular' => true]);
        $routes = $router->getRoutes();
        $this->assertCount(6, $routes);

        // Test singular string specification creates only 6 singular routes.
        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['singular' => 'foo']);
        $routes = $router->getRoutes();
        $this->assertCount(6, $routes);

        // Test singular array specification creates only 6 singular routes.
        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['singular' => ['foo']]);
        $routes = $router->getRoutes();
        $this->assertCount(6, $routes);

        // Test "index" route does not exist.
        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['only' => ['index'], 'singular' => true]);
        $routes = $router->getRoutes();
        $this->assertEmpty($routes);

        // Test specifying incorrect resource generates normal routes.
        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['singular' => ['bar']]);
        $routes = $router->getRoutes();
        $this->assertCount(7, $routes);

        // Test singular route url generation.
        $router = $this->getRouter();
        $router->resource('foo', 'FooController', ['only' => ['show', 'edit', 'update', 'destroy'], 'singular' => true]);
        $routes = $router->getRoutes();

        $this->assertEquals('foo', $routes->getByName('foo.show')->uri());
        $this->assertEquals('foo/edit', $routes->getByName('foo.edit')->uri());
        $this->assertEquals('foo', $routes->getByName('foo.update')->uri());
        $this->assertEquals('foo', $routes->getByName('foo.destroy')->uri());

        // Test nested singular boolean specification.
        $router = $this->getRouter();
        $router->resource('foo.bar', 'FooController', ['only' => ['show'], 'singular' => true]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/{foo}/bar', $routes[0]->uri());

        // Test nested singular string specification.
        $router = $this->getRouter();
        $router->resource('foo.bar', 'FooController', ['only' => ['show'], 'singular' => 'bar']);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/{foo}/bar', $routes[0]->uri());

        // Test nested singular array specification.
        $router = $this->getRouter();
        $router->resource('foo.bar', 'FooController', ['only' => ['show'], 'singular' => ['bar']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/{foo}/bar', $routes[0]->uri());

        // Test parent singular route.
        $router = $this->getRouter();
        $router->resource('foo.bar', 'FooController', ['only' => ['show'], 'singular' => ['foo']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/bar/{bar}', $routes[0]->uri());

        // Test all singular routes.
        $router = $this->getRouter();
        $router->resource('foo.bar', 'FooController', ['only' => ['show'], 'singular' => ['foo', 'bar']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/bar', $routes[0]->uri());

        // Test singular route under parent segment.
        $router = $this->getRouter();
        $router->resource('foo/bar', 'FooController', ['only' => ['show'], 'singular' => true]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/bar', $routes[0]->uri());

        // Test nested singular route under parent segment.
        $router = $this->getRouter();
        $router->resource('foo/bar.baz', 'FooController', ['only' => ['show'], 'singular' => true]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/bar/{bar}/baz', $routes[0]->uri());

        // Test parent singular route under parent segment.
        $router = $this->getRouter();
        $router->resource('foo/bar.baz', 'FooController', ['only' => ['show'], 'singular' => ['bar']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/bar/baz/{baz}', $routes[0]->uri());

        // Test all singular routes under parent segment.
        $router = $this->getRouter();
        $router->resource('foo/bar.baz', 'FooController', ['only' => ['show'], 'singular' => ['bar', 'baz']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foo/bar/baz', $routes[0]->uri());
    }

    public function testResourceRoutingParameters()
    {
        // Route parameters aren't configurable until 5.2.20
        if (! method_exists(ResourceRegistrar::class, 'singularParameters')) {
            $this->markTestSkipped('Route parameters are not configurable in this version.');
            return;
        }

        $router = $this->getRouter();
        $router->resource('foos', 'FooController');
        $router->resource('foos.bars', 'FooController');
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foos/{foo}', $routes[3]->uri());
        $this->assertEquals('foos/{foo}/bars/{bar}', $routes[10]->uri());

        ResourceRegistrar::setParameters(['foos' => 'oof', 'bazs' => 'b']);

        $router = $this->getRouter();
        $router->resource('bars.foos.bazs', 'FooController');
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('bars/{bar}/foos/{oof}/bazs/{b}', $routes[3]->uri());

        ResourceRegistrar::setParameters();
        ResourceRegistrar::singularParameters(false);

        $router = $this->getRouter();
        $router->resource('foos', 'FooController', ['parameters' => 'singular']);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foos/{foo}', $routes[3]->uri());

        // PendingResourceRegistration class doesn't exist until 5.5
        if (class_exists(\Illuminate\Routing\PendingResourceRegistration::class)) {
            $router = $this->getRouter();
            $router->resource('foos', 'FooController', ['parameters' => 'singular']);
            $router->resource('foos.bars', 'FooController')->parameters('singular');
            $routes = $router->getRoutes();
            $routes = $routes->getRoutes();

            $this->assertEquals('foos/{foo}', $routes[3]->uri());
            $this->assertEquals('foos/{foo}/bars/{bar}', $routes[10]->uri());
        }

        $router = $this->getRouter();
        $router->resource('foos.bars', 'FooController', ['parameters' => ['foos' => 'foo', 'bars' => 'bar']]);
        $routes = $router->getRoutes();
        $routes = $routes->getRoutes();

        $this->assertEquals('foos/{foo}/bars/{bar}', $routes[3]->uri());

        if (class_exists(\Illuminate\Routing\PendingResourceRegistration::class)) {
            $router = $this->getRouter();
            $router->resource('foos.bars', 'FooController')->parameter('foos', 'foo')->parameter('bars', 'bar');
            $routes = $router->getRoutes();
            $routes = $routes->getRoutes();

            $this->assertEquals('foos/{foo}/bars/{bar}', $routes[3]->uri());
        }
    }

    protected function getRouter()
    {
        $container = new Container;

        $router = new Router(new Dispatcher, $container);

        $container->singleton(Registrar::class, function () use ($router) {
            return $router;
        });

        $container->instance(Router::class, $router);
        (new \ShiftOneLabs\SingularResourceRoutes\SingularResourceRoutesServiceProvider($container))->register();

        return $router;
    }
}
