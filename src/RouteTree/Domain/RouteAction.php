<?php

namespace Webflorist\RouteTree\Domain;

use Illuminate\Routing\Route;
use Webflorist\RouteTree\Domain\Traits\CanHaveParameterRegex;
use Webflorist\RouteTree\Domain\Traits\CanHaveSegments;
use Webflorist\RouteTree\Exceptions\NodeNotFoundException;
use Webflorist\RouteTree\Exceptions\UrlParametersMissingException;
use Webflorist\RouteTree\RouteTree;
use Webflorist\RouteTree\Services\RouteUrlBuilder;

class RouteAction
{

    use CanHaveSegments, CanHaveParameterRegex;

    /**
     * The route-node this action belongs to.
     *
     * @var RouteNode
     */
    protected $routeNode = null;

    /**
     * Name of the action (e.g. index|create|show|get etc.)
     *
     * @var string
     */
    protected $name = null;

    /**
     * The closure to be used for this action.
     *
     * @var \Closure
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
     * @var string
     */
    private $view;

    /**
     * @var array
     */
    private $viewData;

    /**
     * @var string
     */
    private $redirect;

    /**
     * @var int
     */
    private $redirectStatus;

    /**
     * Array of middleware, this action should be registered with.
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
        if (!is_null($name)) {
            $this->name($name);
        }
        return $this;
    }

    /**
     * Set the name of this action.
     *
     * @param string $name
     */
    public function name(string $name)
    {
        $this->name = $name;
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
     * Returns list of all possible actions, their method, route-name-suffix, parent-action and title-closure.
     *
     * @return array
     */
    public function getActionConfigs()
    {
        return [
            'index' => [
                'method' => 'get',
                'suffix' => 'index',
                'defaultTitle' => function () {
                    return $this->routeNode->getTitle();
                },
                'defaultNavTitle' => function () {
                    return $this->routeNode->getNavTitle();
                }
            ],
            'create' => [
                'method' => 'get',
                'suffix' => 'create',
                'parentAction' => 'index',
                'defaultTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.createTitle', ['resource' => $this->routeNode->getTitle()]);
                },
                'defaultNavTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.createNavTitle');
                }
            ],
            'store' => [
                'method' => 'post',
                'suffix' => 'store'
            ],
            'show' => [
                'method' => 'get',
                'suffix' => 'show',
                'parentAction' => 'index',
                'defaultTitle' => function () {
                    return $this->routeNode->getTitle() . ': ' . $this->routeNode->getActiveValue();
                },
                'defaultNavTitle' => function () {
                    return $this->routeNode->getActiveValue();
                }
            ],
            'edit' => [
                'method' => 'get',
                'suffix' => 'edit',
                'parentAction' => 'show',
                'defaultTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.editTitle', ['item' => $this->routeNode->getActiveValue()]);
                },
                'defaultNavTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.editNavTitle');
                }
            ],
            'update' => [
                'method' => 'put',
                'suffix' => 'update',
                'parentAction' => 'index'
            ],
            'destroy' => [
                'method' => 'delete',
                'suffix' => 'destroy',
                'parentAction' => 'index'
            ],
            'get' => [
                'method' => 'get'
            ],
            'post' => [
                'method' => 'post'
            ],
        ];
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
     * @param \Closure|array|string|callable|null $name
     * @return RouteAction
     */
    public function setAction($name)
    {
        // TODO: add support for various types of $action;
        if (is_string($name) && strpos($name, '@') > 0) {
            $this->setUses($name);
        } else if (is_array($name) && (count($name) === 2) && isset($name['view']) && isset($name['data'])) {
            $this->setView($name['view'], $name['data']);
        } else if (is_array($name) && (count($name) === 2) && isset($name['redirect']) && isset($name['status'])) {
            $this->setRedirect($name['redirect'], $name['status']);
        } else if ($name instanceof \Closure) {
            $this->setClosure($name);
        }
        return $this;
    }

    /**
     * Set the action-string of this action (e.g. index|update|destroy|get|put etc.).
     *
     * @param string $action
     * @return RouteAction
     */
    public function setAction_OLD($action)
    {
        $actionConfigs = $this->getActionConfigs();
        if (!isset($actionConfigs[$action])) {
            // TODO: throw exception
        }
        $this->name = $action;
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
     */
    protected function accumulateParentActions(&$parentActions)
    {

        $actionConfigs = $this->getActionConfigs();
        if (isset($actionConfigs[$this->name]['parentAction'])) {
            $parentActionName = $actionConfigs[$this->name]['parentAction'];
            $parentAction = $this->routeNode->getAction($parentActionName);
            array_push($parentActions, $parentAction);
            $parentAction->accumulateParentActions($parentActions);
        }

    }

    /**
     * Set the closure this action should call.
     *
     * @param \Closure $closure
     * @return RouteAction
     */
    public function setClosure($closure)
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
     * @return mixed
     * @throws NodeNotFoundException
     */
    public function getUrl($parameters = null, $locale = null, $absolute = null)
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
     *
     * @throws Exceptions\RouteNameAlreadyRegisteredException
     * @throws Exceptions\NodeNotFoundException
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

            // And register the generated route with the RouteTree service about this registered route,
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
        return array_merge($this->routeNode->wheres, $this->wheres);
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
     * Checks, if the current action is active (optionally with the desired parameters).
     *
     * @param null $parameters
     * @return string
     */
    public function isActive($parameters = null)
    {

        // Check, if the current action is identical to this node.
        if (route_tree()->getCurrentAction() === $this) {

            // If no parameters are specifically requested, we immediately return true.
            if (is_null($parameters)) {
                return true;
            }

            // If a set of parameters should also be checked, we get the current route-parameters,
            // check if each one is indeed set, and return the boolean result.
            $currentParameters = \Route::current()->parameters();
            $allParametersSet = true;
            foreach ($parameters as $desiredParameterName => $desiredParameterValue) {
                if (!isset($currentParameters[$desiredParameterName]) || ($currentParameters[$desiredParameterName] !== $desiredParameterValue)) {
                    $allParametersSet = false;
                }
            }
            return $allParametersSet;
        }

        return false;
    }

    /**
     * @param string $locale
     * @return Route
     * @throws Exceptions\NodeNotFoundException
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
                route_tree()->getNode($this->redirect)->getPath($locale),
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

    public function isExcludedFromSitemap()
    {
        return
            $this->routeNode->payload->get('sitemap', $this->getName()) &&
            $this->routeNode->isExcludedFromSitemap();
    }

    public function isRedirect()
    {
        return !is_null($this->redirect);
    }

    public function hasParameters()
    {
        return count($this->getPathParameters()) > 0;
    }

    public function hasMiddleware(string $middleware)
    {
        return array_search($middleware, $this->compileMiddleware()) !== false;
    }

    public function hasParameterValues(string $locale)
    {
        $rootLineParameters =  $this->routeNode->getRootLineParameters();
        foreach ($this->getPathParameters($locale) as $pathParameter) {
            if (!isset($rootLineParameters[$pathParameter])) {
                return false;
            }
            if (!$rootLineParameters[$pathParameter]->hasValues($locale)) {
                return false;
            }
        }
        return true;
    }

}