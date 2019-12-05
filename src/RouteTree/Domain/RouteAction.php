<?php

namespace Webflorist\RouteTree\Domain;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Webflorist\RouteTree\Domain\Traits\CanHaveSegments;
use Webflorist\RouteTree\Exceptions\ActionNotFoundException;
use Webflorist\RouteTree\Exceptions\NodeNotFoundException;
use Webflorist\RouteTree\RouteTree;
use Webflorist\RouteTree\Services\RouteUrlBuilder;

/**
 * Class RouteAction
 *
 * A RouteAction is responsible of generating
 * Laravel-Routes for a specific action
 * (e.g. index|create|show|get etc.).
 *
 * @package Webflorist\RouteTree\Domain
 */
class RouteAction
{

    use CanHaveSegments;

    /**
     * The RouteNode this RouteAction belongs to.
     *
     * @var RouteNode
     */
    protected $routeNode = null;

    /**
     * Name of the action (e.g. index|create|show|get etc.).
     *
     * Defaults to the HTTP-method.
     * Can be overridden via name().
     *
     * @var string
     */
    protected $name = null;

    /**
     * The closure to be used for this action.
     *
     * @var Closure
     */
    protected $closure = null;

    /**
     * The controller-method to be used for this action.
     *
     * @var string
     */
    protected $uses = null;

    /**
     * The full paths, this action was generated with.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * HTTP verb for this action.
     *
     * @var string
     */
    private $method;

    /**
     * The view to be used for this action.
     *
     * @var string
     */
    private $view;

    /**
     * Any view-data to be used for this action.
     *
     * @var array
     */
    private $viewData;

    /**
     * The redirect-target to be used for this action.
     *
     * @var string
     */
    private $redirect;

    /**
     * The redirect-status to be used for this action.
     *
     * Defaults to 302.
     *
     * @var int
     */
    private $redirectStatus;

    /**
     * Array of middleware, this action should be registered with.
     * (in addition to the middleware inherited from it's RouteNode
     * and the RouteNode's parents).
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Array of inherited middleware that should be skipped by this action.
     *
     * @var array
     */
    protected $skipMiddleware = [];

    /**
     * Any action-specific RoutePayload.
     *
     * Can be used to override the RouteNode's payload.
     *
     * @var RoutePayload
     */
    public $payload;

    /**
     * RouteAction constructor.
     *
     * @param string $method
     * @param string $action
     * @param RouteNode $routeNode
     * @param string|null $name
     */
    public function __construct(string $method, $action, RouteNode $routeNode, ?string $name = null)
    {
        $this->method = $method;
        $this->routeNode = $routeNode;
        $this->setAction($action);
        $this->payload = new RoutePayload($routeNode, $this);
        if (!is_null($name)) {
            $this->name($name);
        }
        return $this;
    }

    /**
     * Set the name of this action.
     *
     * @param string $name
     * @return RouteAction
     */
    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of this action.
     *
     * @return string
     */
    public function getName()
    {
        if (!is_null($this->name)) {
            return $this->name;
        }
        return $this->method;
    }

    /**
     * Adds a single middleware to this action.
     *
     * @param string $name Name of the middleware.
     * @param array $parameters Parameters the middleware should be called with.
     * @return RouteAction
     */
    public function middleware(string $name, array $parameters = [])
    {
        $this->middleware[$name] = $parameters;
        return $this;
    }

    /**
     * Skip an inherited middleware.
     *
     * @param string $name Name of the middleware.
     * @return RouteAction
     */
    public function skipMiddleware(string $name)
    {
        if (array_search($name, $this->skipMiddleware) === false) {
            $this->skipMiddleware[] = $name;
        }
        return $this;
    }

    /**
     * Get the route-node this action belongs to.
     *
     * @return RouteNode
     */
    public function getRouteNode()
    {
        return $this->routeNode;
    }

    /**
     * Set the action (controller-method, view, redirect, closure, etc.)
     * this RouteAction should use.
     *
     * @param Closure|array|string|callable|null $name
     * @return RouteAction
     */
    public function setAction($name)
    {
        if (is_string($name) && strpos($name, '@') > 0) {
            $this->setUses($name);
        } else if (is_array($name) && (count($name) === 2) && isset($name['view']) && isset($name['data'])) {
            $this->setView($name['view'], $name['data']);
        } else if (is_array($name) && (count($name) === 2) && isset($name['redirect']) && isset($name['status'])) {
            $this->setRedirect($name['redirect'], $name['status']);
        } else if ($name instanceof Closure) {
            $this->setClosure($name);
        }
        return $this;
    }

    /**
     * Gets an array of all hierarchical actions of this node and all parent nodes
     * (with the root-node-action as the first element).
     *
     * This is very useful for breadcrumbs.
     *
     * E.g. The edit action of the node 'user.comment'
     * with path '/user/{user}/comments/{comment}/edit' consists of the following parent actions:
     *
     * - the default-action of the root-node with path '/'
     * - the index-action of the node 'user' with path '/user'
     * - the show-action of the node 'user' with path '/user/{user}'
     * - the index action of the node 'user.comment' with path '/user/{user}/comments'
     * - the show action of the node 'user.comment' with path '/user/{user}/comments/{comment}'
     *
     * @return RouteAction[]
     */
    public function getRootLineActions()
    {

        $rootLineActions = [];

        $this->accumulateRootLineActions($rootLineActions);

        $rootLineActions = array_reverse($rootLineActions);

        return $rootLineActions;
    }

    /**
     * Accumulate all parent actions of this and any parent nodes represented in the path for this action.
     *
     * @param $rootLineActions
     */
    protected function accumulateRootLineActions(&$rootLineActions)
    {

        $this->accumulateParentActions($rootLineActions);
        if ($this->routeNode->hasParentNode()) {
            $mostActiveRootLineAction = $this->routeNode->getParentNode()->getLowestRootLineAction();
            if ($mostActiveRootLineAction !== false) {
                array_push($rootLineActions, $mostActiveRootLineAction);
                $mostActiveRootLineAction->accumulateRootLineActions($rootLineActions);
            }
        }

    }

    /**
     * Accumulate all parent-actions within the same routeNode for this action.
     * E.g.
     * The action 'edit' with it's path 'user/{user}/edit' is a child of
     * the action 'show' with it's path 'user/{user]', which is itself a child of
     * the action 'index' with it's path 'user'.
     *
     * @param $parentActions
     * @throws ActionNotFoundException
     */
    protected function accumulateParentActions(&$parentActions)
    {
        $parentActionMappings = [
            'create' => 'index',
            'show' => 'index',
            'edit' => 'show',
            'update' => 'index',
            'destroy' => 'index'
        ];
        if (isset($parentActionMappings[$this->name])) {
            $parentActionName = $parentActionMappings[$this->name];
            $parentAction = $this->routeNode->getAction($parentActionName);
            array_push($parentActions, $parentAction);
            $parentAction->accumulateParentActions($parentActions);
        }
    }

    /**
     * Set the closure this action should call.
     *
     * @param Closure $closure
     * @return RouteAction
     */
    private function setClosure($closure)
    {
        $this->closure = $closure;
        return $this;
    }

    /**
     * Set 'controller@method', this action will use.
     *
     * @param string $uses
     * @return RouteAction
     */
    private function setUses($uses)
    {
        $this->uses = $uses;
        return $this;
    }

    /**
     * Set view and view-data, this action will use.
     *
     * @param string $view
     * @param array $data
     * @return void
     */
    private function setView(string $view, array $data = [])
    {
        $this->view = $view;
        $this->viewData = $data;
    }


    /**
     * Set redirect and status-code, this action will use.
     *
     * @param string $redirect
     * @param int $status
     * @return void
     */
    private function setRedirect(string $redirect, int $status = 302)
    {
        $this->redirect = $redirect;
        $this->redirectStatus = $status;
    }

    /**
     * Get the URL to this action.
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * @param string $locale The language this url should be generated for (default=current locale).
     * @param bool $absolute Create absolute paths instead of relative paths (default=true/configurable).
     * @return RouteUrlBuilder
     * @throws NodeNotFoundException
     */
    public function getUrl($parameters = null, $locale = null, $absolute = null): RouteUrlBuilder
    {
        return new RouteUrlBuilder($this->routeNode, $this->getName(), $parameters, $locale, $absolute);
    }

    /**
     * Returns an array of all path-parameters needed for this RouteAction
     * These are basically all path-segments enclosed in curly braces.
     *
     * @param null $locale
     * @return array
     */
    public function getPathParameters($locale = null)
    {

        // If no language is specifically stated, we use the current locale.
        RouteTree::establishLocale($locale);

        $parameters = [];
        $pathSegments = explode('/', $this->paths[$locale]);
        foreach ($pathSegments as $segment) {
            if ((substr($segment, 0, 1) === '{') && (substr($segment, -1) === '}')) {
                array_push($parameters, str_replace('{', '', str_replace('}', '', $segment)));
            }
        }

        return $parameters;

    }

    /**
     * Generate routes in each language for this action.
     */
    public function generateRoutes()
    {

        // Compile the middleware.
        $middleware = $this->compileMiddleware();

        // Compile parameter regexes.
        $parameterRegex = $this->compileParameterRegex();

        // Iterate through configured languages
        // and build routes.
        foreach ($this->routeNode->getLocales() as $locale) {

            $route = $this->createRoute($locale);

            $route->name(
                $this->getRouteName($locale)
            );

            $route->middleware(
                $middleware
            );

            $route->where(
                $parameterRegex
            );

            // And register the generated route with the RouteTree service,
            // so it can manage a static list.
            route_tree()->registerRoute($route, $this, $locale);

        }

    }

    /**
     * Generates the compiled middleware-array to be handed over to the laravel-route-generator.
     *
     * @return array
     */
    private function compileMiddleware()
    {

        // Get the middleware from the node (except this action is configured to skip it).
        $middleware = [];
        foreach ($this->routeNode->getMiddleware() as $middlewareKey => $middlewareParams) {
            if (array_search($middlewareKey, $this->skipMiddleware) === false) {
                $middleware[$middlewareKey] = $middlewareParams;
            }
        }

        // Merge it with middleware set within this action.
        $middleware = array_merge($middleware, $this->middleware);

        // Compile it into laravel-syntax.
        $compiledMiddleware = [];
        if (count($middleware) > 0) {
            foreach ($middleware as $middlewareName => $middlewareParams) {
                $compiledMiddleware[$middlewareName] = $middlewareName;
                if (count($middlewareParams) > 0) {
                    $compiledMiddleware[$middlewareName] .= ':' . implode(',', $middlewareParams);
                }
            }
        }

        return $compiledMiddleware;
    }

    /**
     * Generates the compiled array of parameter-regexes (wheres)
     * to be handed over to the laravel-route-generator.
     *
     * @return array
     */
    private function compileParameterRegex()
    {
        $parameterRegex = [];
        foreach ($this->routeNode->getRootLineParameters() as $routeParameter) {
            if ($routeParameter->hasRegex()) {
                $parameterRegex[$routeParameter->getName()] = $routeParameter->getRegex();
            }
        }
        return $parameterRegex;
    }

    /**
     * Get full route-name for this action for a specific language.
     *
     * @param $locale
     * @return string
     */
    public function getRouteName($locale)
    {

        // A route name always starts with the locale.
        $routeName = $locale;

        // Then we append the id of the route-node.
        if (strlen($this->routeNode->getId()) > 0) {
            $routeName .= '.' . $this->routeNode->getId();
        }

        $routeName .= '.' . $this->getName();

        return $routeName;
    }

    /**
     * Checks, if the current action is active.
     *
     * (Optionally with the desired [parameter => routeKey] pairs.)
     *
     * @param array|null $parameters
     * @return string
     */
    public function isActive(?array $parameters = null)
    {

        // Check, if the current action is identical to this node.
        if (route_tree()->getCurrentAction() === $this) {

            // If no parameters are specifically requested, we immediately return true.
            if (is_null($parameters)) {
                return true;
            }

            // If a set of parameters should also be checked,
            // check, if the current route has them.
            return RouteParameter::currentRouteHasRouteKeys($parameters);

        }

        return false;
    }

    /**
     * Generates a Laravel Route in the specified language.
     *
     * @param string $locale
     * @return Route
     */
    private function createRoute(string $locale): Route
    {
        $uri = $this->generateUri($locale);

        // In case of a View Route...
        if (!is_null($this->view)) {
            return \Illuminate\Support\Facades\Route::view(
                $uri,
                $this->view,
                $this->viewData
            );
        }

        // In case of a Redirect Route...
        if (!is_null($this->redirect)) {
            return \Illuminate\Support\Facades\Route::redirect(
                $uri,
                '/' . route_tree()->getNode($this->redirect)->getPath($locale),
                $this->redirectStatus
            );
        }

        // In case of a regular action route...
        $action = [];
        if (!is_null($this->uses)) {
            $action['uses'] = $this->uses;
            if (substr($this->uses, 0, 1) !== '\\') {
                $action['uses'] = $this->routeNode->getNamespace() . '\\' . $this->uses;
            }
        } else if (is_callable($this->closure)) {
            array_push($action, $this->closure);
        }
        return \Illuminate\Support\Facades\Route::{$this->method}($uri, $action);
    }

    /**
     * Generates the full Uri to this action
     * for a specified language.
     *
     * @param string $locale
     * @return string
     */
    private function generateUri(string $locale): string
    {
        // Get the uri for this route-node and locale to register this route with.
        $uri = $this->routeNode->getPath($locale);

        // Append any action specific segment.
        if ($this->hasSegment($locale)) {
            $uri .= '/' . $this->getSegment($locale);
        }

        // Save the generated uri to $this->paths
        $this->paths[$locale] = $uri;

        return $uri;
    }

    /**
     * Get all locales this RouteAction should be registered with.
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->routeNode->getLocales();
    }

    /**
     * Is this RouteAction a redirect?
     *
     * @return bool
     */
    public function isRedirect()
    {
        return !is_null($this->redirect);
    }

    /**
     * Does this action have any parameters in it's path.
     *
     * @return bool
     */
    public function hasParameters()
    {
        return count($this->getPathParameters()) > 0;
    }

    /**
     * Check if this action uses the specified middleware.
     *
     * @param string $middleware
     * @return bool
     */
    public function hasMiddleware(string $middleware): bool
    {
        return array_search($middleware, $this->compileMiddleware()) !== false;
    }

    /**
     * Are all route-parameters resolvable into their possible values?
     *
     * @param string $locale
     * @return bool
     */
    public function canResolveAllRouteKeys(string $locale): bool
    {
        $rootLineParameters = $this->routeNode->getRootLineParameters();
        foreach ($this->getPathParameters($locale) as $pathParameter) {
            if (!isset($rootLineParameters[$pathParameter])) {
                return false;
            }
            if (!$rootLineParameters[$pathParameter]->canResolveRouteKeyList($locale)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns array of RouteParameter-objects
     * for all parameters present in the
     * path to this action.
     *
     * @return array
     */
    public function getRootLineParameters()
    {
        return Arr::only($this->routeNode->getRootLineParameters(), $this->getPathParameters());
    }

    /**
     * Get the page title of this action (defaults to $this->routeNode->getTitle()).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @return string
     * @throws ActionNotFoundException
     */
    public function getTitle(?array $parameters = null, ?string $locale = null): string
    {

        $title = $this->payload->get('title', $parameters, $locale);

        if (is_string($title)) {
            return $title;
        }

        // Fallback for resources is to get the action specific default-title from the RouteResource.
        if ($this->routeNode->isResource()) {
            return $this->routeNode->resource->getActionTitle($this->getName(), $parameters, $locale);
        }

        // Per default we fall back to $this->routeNode->getTitle().
        return $this->routeNode->getTitle($parameters, $locale, false);
    }

    /**
     * Get the navigation title of this action (defaults to $this->routeNode->getNavTitle()).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @return string
     * @throws ActionNotFoundException
     */
    public function getNavTitle(?array $parameters = null, ?string $locale = null): string
    {
        $title = $this->payload->get('navTitle', $parameters, $locale);

        if (is_string($title)) {
            return $title;
        }

        // Fallback for resources is to get the action specific default-navTitle from the RouteResource.
        if ($this->routeNode->isResource()) {
            return $this->routeNode->resource->getActionNavTitle($this->getName(), $parameters, $locale);
        }

        // Per default we fall back to $this->routeNode->getNavTitle().
        return $this->routeNode->getNavTitle($parameters, $locale, false);
    }

}