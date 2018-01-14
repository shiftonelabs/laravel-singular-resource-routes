<?php

namespace ShiftOneLabs\SingularResourceRoutes;

use Illuminate\Support\Str;
use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;

class ResourceRegistrar extends BaseResourceRegistrar
{
    /**
     * The default actions for a singular resourceful controller.
     *
     * @var array
     */
    protected $singularResourceDefaults = ['create', 'store', 'show', 'edit', 'update', 'destroy'];

    /**
     * The resources to treat as singular.
     *
     * @var array
     */
    protected $singularResources = [];

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     *
     * @return void
     */
    public function register($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && !isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these resource routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
        if (Str::contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);

            return;
        }

        $resources = explode('.', $name);

        // If the singular option is set, we need to determine which resources are meant
        // to be singular resources. If set to true, then only the last resource will
        // be singular, otherwise intersect all resources with the given resources.
        if (!empty($options['singular'])) {
            $singular = $options['singular'];
            $this->singularResources = array_intersect(
                $resources,
                $singular === true ? [last($resources)] : (!is_array($singular) ? [$singular] : $singular)
            );
        }

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
        $base = $this->getResourceWildcard(last($resources));

        $defaults = $this->getResourceDefaults(last($resources));

        foreach ($this->getResourceMethods($defaults, $options) as $m) {
            $this->{'addResource'.ucfirst($m)}($name, $base, $controller, $options);
        }
    }

    /**
     * Get the default resource methods defaults for the given resource.
     *
     * @param  string  $resource
     *
     * @return array
     */
    protected function getResourceDefaults($resource)
    {
        if (in_array($resource, $this->singularResources)) {
            return $this->singularResourceDefaults;
        }

        return $this->resourceDefaults;
    }

    /**
     * Add the show method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     *
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceShow($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).$this->getResourceUriParameter($base);

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the edit method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     *
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceEdit($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).$this->getResourceUriParameter($base).'/'.$this->getVerb('edit');

        $action = $this->getResourceAction($name, $controller, 'edit', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the update method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     *
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceUpdate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).$this->getResourceUriParameter($base);

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the destroy method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     *
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceDestroy($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).$this->getResourceUriParameter($base);

        $action = $this->getResourceAction($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Get the URI for a nested resource segment array.
     *
     * @param  array  $segments
     *
     * @return string
     */
    protected function getNestedResourceUri(array $segments)
    {
        // We will spin through the segments and create a place-holder for each of the
        // resource segments, as well as the resource itself. Then we should get an
        // entire string for the resource URI that contains all nested resources.
        return implode('/', array_map(function ($s) {
            return $s.$this->getResourceUriParameter($this->getResourceWildcard($s));
        }, $segments));
    }

    /**
     * Format a resource parameter for usage.
     *
     * @param  string  $value
     *
     * @return string|null
     */
    public function getResourceWildcard($value)
    {
        if (in_array($value, $this->singularResources)) {
            return;
        }

        // parameters/parameterMap properties do not exist until 5.2.20
        if (property_exists($this, 'parameters')) {
            if (isset($this->parameters[$value])) {
                $value = $this->parameters[$value];
            } elseif (isset(static::$parameterMap[$value])) {
                $value = static::$parameterMap[$value];
            } elseif ($this->parameters === 'singular' || static::$singularParameters) {
                $value = Str::singular($value);
            }
        }

        return str_replace('-', '_', $value);
    }

    /**
     * Get the parameter name as a resource URI parameter segment.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function getResourceUriParameter($value)
    {
        if (empty($value)) {
            return '';
        }

        return '/{'.$value.'}';
    }

    /**
     * Get the url verb for the specified verb.
     *
     * @return string
     */
    protected function getVerb($verb)
    {
        // verbs property does not exist until 5.3.27
        if (property_exists(get_class(), 'verbs') && !empty(static::$verbs[$verb])) {
            return static::$verbs[$verb];
        }

        return $verb;
    }
}
