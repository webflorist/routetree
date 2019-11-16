<?php

namespace Webflorist\RouteTree\Domain;


use Webflorist\RouteTree\Exceptions\UrlParametersMissingException;
use Webflorist\RouteTree\RouteTree;

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
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $routeName;

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

    public function method(string $method)
    {
        $this->method = $method;
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


}
