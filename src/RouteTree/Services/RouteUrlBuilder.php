<?php

namespace Webflorist\RouteTree\Services;

use Webflorist\RouteTree\Domain\RouteAction;
use Webflorist\RouteTree\Domain\RouteNode;
use Webflorist\RouteTree\Exceptions\ActionNotFoundException;
use Webflorist\RouteTree\RouteTree;

class RouteUrlBuilder
{
    /**
     * @var RouteNode
     */
    private $routeNode;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array|null
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @param string $action
     * @return RouteUrlBuilder
     */
    public function action(string $action): RouteUrlBuilder
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param array|null $parameters
     * @return RouteUrlBuilder
     */
    public function parameters(array $parameters): RouteUrlBuilder
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param string|null $locale
     * @return RouteUrlBuilder
     */
    public function locale(string $locale): RouteUrlBuilder
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param bool|null $absolute
     * @return RouteUrlBuilder
     */
    public function absolute(bool $absolute=true): RouteUrlBuilder
    {
        $this->absolute = $absolute;
        return $this;
    }

    /**
     * @var bool|null
     */
    private $absolute;

    /**
     * RouteUrlBuilder constructor.
     *
     * @param string $nodeId The node-id for which this url is generated (default=current node).
     * @param string $action The node-action for which this url is generated (default='index|get').
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * @param string $locale The language this url should be generated for (default=current locale).
     * @param bool $absolute Create absolute paths instead of relative paths (default=true/configurable).
     * @return string
     */
    public function __construct($nodeId=null, $action=null, $parameters = null, $locale=null, $absolute=null)
    {

        if (is_null($nodeId)) {
            $this->routeNode = route_tree()->getCurrentNode();
        }
        else {
            $this->routeNode = route_tree()->getNode($nodeId);
        }

        if (is_null($action)) {
            if ($this->routeNode->hasAction('index')) {
                $this->action = 'index';
            }
            else if ($this->routeNode->hasAction('get')) {
                $this->action = 'get';
            }
        }
        $this->parameters = $parameters;
        $this->locale = $locale;
        $this->absolute = $absolute;
    }

    public function __toString()
    {
        $locale = $this->locale;
        // If no language is specifically stated, we use the current locale.
        RouteTree::establishLocale($locale);

        $routeAction = $this->getRouteAction();

        $absolute = $this->absolute;
        if (is_null($this->absolute)) {
            $absolute = config('routetree.absolute_urls');
        }

        return route(
            $routeAction->getRouteName($locale),
            $routeAction->autoFillPathParameters($this->parameters, $locale, true),
            $absolute
        );
    }

    private function getRouteAction() : RouteAction
    {
        if ($this->routeNode->hasAction($this->action)) {
            return $this->routeNode->getAction($this->action);
        }

        throw new ActionNotFoundException('Node with Id "' . $this->routeNode->getId() . '" does not have the action "' . $this->action . '""');
    }


}