<?php

namespace Webflorist\RouteTree\Domain;


use Illuminate\Routing\Route;
use Illuminate\Support\Collection;

/**
 * A Route registered with RouteTree.
 *
 * Class RegisteredRoute
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
     * @var RouteAction
     */
    public $routeAction;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $methods;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $routeName;

    /**
     * @var Route
     */
    public $route;

    /**
     * @var array
     */
    public $routeKeys;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function routeNode(RouteNode $routeNode)
    {
        $this->routeNode = $routeNode;
        return $this;
    }

    public function routeAction(RouteAction $routeAction)
    {
        $this->routeAction = $routeAction;
        return $this;
    }

    public function locale(string $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function methods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    public function path(string $path)
    {
        $this->path = $path;
        return $this;
    }

    public function routeName(string $routeName)
    {
        $this->routeName = $routeName;
        return $this;
    }

    public function routeKeys(array $parameters)
    {
        $this->routeKeys = $parameters;
        return $this;
    }

    public function hasMethod(string $method)
    {
        return array_search(strtoupper($method), $this->methods) !== false;
    }

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
                        ->path(substr(route($this->routeName, $routeKeySet, false),1))
                );
            }
        }
        return $registeredRoutes;
    }

    /**
     * @param RouteParameter[] $routeParameters
     * @param array $currentSet
     * @param array $routeKeySets
     */
    protected function fillRouteKeySets(array $routeParameters, array &$routeKeySets, ?array &$currentSet=null)
    {
        $nextParameter = array_shift($routeParameters);
        foreach ($nextParameter->getRouteKeys($this->locale, $currentSet) as $paramValue) {
            if (is_null($currentSet)) {
                $currentSet = [];
            }
            $currentSet[$nextParameter->getName()] = $paramValue;
            if (count($routeParameters)>0) {
                $this->fillRouteKeySets($routeParameters, $routeKeySets, $currentSet);
            }
            else {
                $routeKeySets[] = $currentSet;
            }
        };
    }


}
