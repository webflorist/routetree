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
    public $parameters = [];

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

    public function parameters(array $parameters)
    {
        $this->parameters = $parameters;
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
            $parameterSets = [];
            /** @var RouteParameter[] $parameters */
            $this->fillParameterSets($this->routeAction->getRootLineParameters(), $parameterSets);
            foreach ($parameterSets as $parameterSet) {
                if ($this->hasMethod('get')) {
                    //dump($this->routeName.':'.$this->path.':'. route($this->routeName, $parameterSet, false));
                }
                $registeredRoutes->add(
                    (new RegisteredRoute($this->route))
                        ->parameters($parameterSet)
                        ->routeAction($this->routeAction)
                        ->routeNode($this->routeNode)
                        ->methods($this->methods)
                        ->locale($this->locale)
                        ->routeName($this->routeName)
                        ->path(substr(route($this->routeName, $parameterSet, false),1))
                );
            }
        }
        return $registeredRoutes;
    }

    /**
     * @param RouteParameter[] $parameters
     * @param array $currentSet
     * @param array $parameterSets
     */
    protected function fillParameterSets(array $parameters, array &$parameterSets, ?array &$currentSet=null)
    {
        $nextParameter = array_shift($parameters);
        foreach ($nextParameter->getValues($this->locale, $currentSet) as $paramValue) {
            if (is_null($currentSet)) {
                $currentSet = [];
            }
            $currentSet[$nextParameter->getName()] = $paramValue;
            if (count($parameters)>0) {
                $this->fillParameterSets($parameters, $parameterSets, $currentSet);
            }
            else {
                $parameterSets[] = $currentSet;
            }
        };
    }


}
