<?php

namespace Webflorist\RouteTree\Domain;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

/**
 * Class RegisteredRoute
 *
 * A Route registered with RouteTree
 * containing all relevant related
 * objects and information.
 *
 * @package Webflorist\RouteTree
 */
class RegisteredRoute
{

    /**
     * The RouteNode this RegisteredRoute belongs to.
     *
     * @var RouteNode
     */
    public $routeNode;

    /**
     * The RouteAction this RegisteredRoute belongs to.
     *
     * @var RouteAction
     */
    public $routeAction;

    /**
     * The language this RegisteredRoute is registered in.
     *
     * @var string
     */
    public $locale;

    /**
     * The HTTP-methods this RegisteredRoute is registered with.
     *
     * @var array
     */
    public $methods;

    /**
     * The URI/path this RegisteredRoute is registered with.
     *
     * @var string
     */
    public $path;

    /**
     * The full route-name of this RegisteredRoute.
     *
     * @var string
     */
    public $routeName;

    /**
     * The Laravel Route object corresponding
     * to this RegisteredRoute.
     *
     * @var Route
     */
    public $route;

    /**
     * Array of route-keys/parameters used
     * to generate this RegisteredRoute.
     *
     * This is mainly used, when a Route with parameters
     * is resolved into it's possible path-instances.
     *
     * @var array
     */
    public $routeKeys;

    /**
     * RegisteredRoute constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Sets the RouteNode this RegisteredRoute belongs to.
     *
     * @param RouteNode $routeNode
     * @return $this
     */
    public function routeNode(RouteNode $routeNode)
    {
        $this->routeNode = $routeNode;
        return $this;
    }

    /**
     * Sets the RouteAction this RegisteredRoute belongs to.
     *
     * @param RouteAction $routeAction
     * @return $this
     */
    public function routeAction(RouteAction $routeAction)
    {
        $this->routeAction = $routeAction;
        return $this;
    }

    /**
     * Sets the language this RegisteredRoute is registered in.
     *
     * @param string $locale
     * @return $this
     */
    public function locale(string $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Sets the HTTP-methods this RegisteredRoute is registered with.
     *
     * @param array $methods
     * @return $this
     */
    public function methods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * Sets the URI/path this RegisteredRoute is registered with.
     *
     * @param string $path
     * @return $this
     */
    public function path(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Sets the full route-name of this RegisteredRoute.
     *
     * @param string $routeName
     * @return $this
     */
    public function routeName(string $routeName)
    {
        $this->routeName = $routeName;
        return $this;
    }

    /**
     * Sets an array of route-keys/parameters used
     * to generate this RegisteredRoute.
     *
     * This is mainly used, when a Route with parameters
     * is resolved into it's possible path-instances.
     *
     * @param array $parameters
     * @return $this
     */
    public function routeKeys(array $parameters)
    {
        $this->routeKeys = $parameters;
        return $this;
    }

    /**
     * Is this RegisteredRoute registered
     * with the HTTP-method $method?
     *
     * @param string $method
     * @return bool
     */
    public function hasMethod(string $method)
    {
        return array_search(strtoupper($method), $this->methods) !== false;
    }

    /**
     * Tries to retrieve the possible route-key/parameter combinations
     * for a route using parameters and clones this RegisteredRoute
     * into multiple objects with routeKeys and path corresponding to
     * the route-keys.
     *
     * @return Collection
     */
    public function getForAllRouteKeys()
    {
        $registeredRoutes = new Collection();

        if ($this->routeAction->hasParameters()) {
            $routeKeySets = [];
            /** @var RouteParameter[] $parameters */
            $this->fillRouteKeySets($this->routeAction->getRootLineParameters(), $routeKeySets);
            foreach ($routeKeySets as $routeKeySet) {
                if ($this->hasMethod('get')) {
                    //dump($this->routeName.':'.$this->path.':'. route($this->routeName, $routeKeySet, false));
                }
                $registeredRoutes->push(
                    (new RegisteredRoute($this->route))
                        ->routeKeys($routeKeySet)
                        ->routeAction($this->routeAction)
                        ->routeNode($this->routeNode)
                        ->methods($this->methods)
                        ->locale($this->locale)
                        ->routeName($this->routeName)
                        ->path(substr(route($this->routeName, $routeKeySet, false), 1))
                );
            }
        }
        return $registeredRoutes;
    }

    /**
     * Traverses all $routeParameters,
     * tries to find out their possible value-combinations
     * and puts them in $routeKeySets.
     *
     * @param RouteParameter[] $routeParameters
     * @param array $currentSet
     * @param array $routeKeySets
     */
    protected function fillRouteKeySets(array $routeParameters, array &$routeKeySets, ?array &$currentSet = null)
    {
        $nextParameter = array_shift($routeParameters);
        foreach ($nextParameter->getRouteKeyList($this->locale, $currentSet) as $paramValue) {
            if (is_null($currentSet)) {
                $currentSet = [];
            }
            $currentSet[$nextParameter->getName()] = $paramValue;
            if (count($routeParameters) > 0) {
                $this->fillRouteKeySets($routeParameters, $routeKeySets, $currentSet);
            } else {
                $routeKeySets[] = $currentSet;
            }
        };
    }


}
