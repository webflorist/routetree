<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 21.04.2016
 * Time: 14:51
 */

namespace Nicat\RouteTree;

class RouteAction
{

    /**
     * Static list of all possible actions, their method, and route-name-suffix.
     *
     * @var array
     */
    public static $possibleActions = [
        'index' => [
            'method' => 'get',
            'suffix' => 'index'
        ],
        'create' => [
            'method' => 'get',
            'suffix' => 'create'
        ],
        'store' => [
            'method' => 'post',
            'suffix' => 'store'
        ],
        'show' => [
            'method' => 'get',
            'suffix' => 'show'
        ],
        'edit' => [
            'method' => 'get',
            'suffix' => 'edit'
        ],
        'update' => [
            'method' => 'put',
            'suffix' => 'update'
        ],
        'destroy' => [
            'method' => 'delete',
            'suffix' => 'destroy'
        ],
        'get' => [
            'method' => 'get'
        ],
        'post' => [
            'method' => 'post'
        ],
    ];

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
    protected $action = null;

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
     * The path-suffix, this action will have on top of it's node's path.
     *
     * @var string
     */
    protected $pathSuffix = null;

    /**
     * RouteAction constructor.
     * @param string $action
     */
    public function __construct($action)
    {
        $this->setAction($action);
        return $this;
    }

    /**
     * Set the path-suffix, this action will have on top of it's node's path.
     *
     * @param string $pathSuffix
     * @return RouteAction
     */
    public function setPathSuffix($pathSuffix)
    {
        $this->pathSuffix = $pathSuffix;
        return $this;
    }

    /**
     * Set the route-node this action belongs to.
     *
     * @param RouteNode $routeNode
     * @return RouteAction
     */
    public function setRouteNode($routeNode)
    {
        $this->routeNode = $routeNode;
        return $this;
    }

    /**
     * Get the action-string.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }


    /**
     * Get the method to be used with this action.
     *
     * @return string
     */
    public function getMethod()
    {
        return self::$possibleActions[$this->action]['method'];
    }

    /**
     * Set the action-string of this action (e.g. index|update|destroy|get|put etc.).
     *
     * @param string $action
     * @return RouteAction
     */
    public function setAction($action)
    {
        if (!isset(self::$possibleActions[$action])) {
            // TODO: throw exception
        }
        $this->action = $action;
        return $this;
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
    public function setUses($uses)
    {
        $this->uses = $uses;
        return $this;
    }

    /**
     * Get the URL to this action.
     *
     * @param array $parameters The values to be used for any route-parameters in the url.
     * @param string $language The language this url should be generated for (default=current locale).
     * @return mixed
     */
    public function getUrl($parameters=null, $language=null) {

        // If no language is specifically stated, we use the current locale
        if (is_null($language)) {
            $language = app()->getLocale();
        }

        // If no parameters are specifically stated, we overtake the current ones, if they are used anywhere in the rootline.
        if (is_null($parameters)) {            
            $parameters = $this->routeNode->getParametersOfNodeAndParents(true, $language);
        }

        // route() wants empty parameters as an empty array.
        if (is_null($parameters)) {
            $parameters = [];
        }

        return route($this->generateRouteName($language), $parameters);

    }

    /**
     * Generate routes in each language for this action.
     */
    public function generateRoutes() {

        // Get the method, that will be used for registering the route with laravel.
        $method = $this->getMethod();

        // Initialize the action-array, that is used to register the route with laravel.
        $action = [];

        // Add the middleware to the action-array.
        $action['middleware'] = $this->routeNode->getMiddleware();

        // Add the controller-method or the closure to the action-array.
        if (!is_null($this->uses)) {
            $action['uses'] = $this->routeNode->getNamespace().'\\'.$this->uses;
        }
        else if (is_callable($this->closure)) {
            array_push($action,$this->closure);
        }

        // Iterate through configured languages.
        foreach (\Config::get('app.locales') as $language => $fullLanguage) {

            // Generate and set route name.
            $action['as'] = $this->generateRouteName($language);

            // Get the path for this route-node and language to register this route with.
            $path = $this->routeNode->getPath($language);

            // Append any configured suffix.
            if (strlen($this->pathSuffix)>0) {
                $path .= $this->pathSuffix;
            }

            // Now register the route with laravel.
            \Route::$method($path, $action);

            // And tell the RouteTree service about this registered route,
            // so it can manage a static list.
            route_tree()->registerPath($path, $this);

        }
        
    }

    /**
     * Generates a full route-name for this action for a specific language.
     *
     * @param $language
     * @return string
     */
    private function generateRouteName($language)
    {

        // A full route name always starts with the language-key.
        $fullRouteName = $language;

        // Then we append the id of the route-node.
        if (strlen($this->routeNode->getId()) > 0) {
            $fullRouteName .= '.' . $this->routeNode->getId();
        }

        // Append the suffix for this action, if defined.
        if (isset(RouteAction::$possibleActions[$this->action]['suffix'])) {
            $fullRouteName .= '.' . self::$possibleActions[$this->action]['suffix'];
        }

        return $fullRouteName;
    }

}