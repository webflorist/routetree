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
     * @var RouteNode
     */
    protected $routeNode = null;

    /**
     * @var string
     */
    protected $action = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var \Closure
     */
    protected $closure = null;

    /**
     * @var string
     */
    protected $uses = null;

    /**
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
     * @return string
     */
    public function getPathSuffix()
    {
        return $this->pathSuffix;
    }

    /**
     * @param string $pathSuffix
     * @return RouteAction
     */
    public function setPathSuffix($pathSuffix)
    {
        $this->pathSuffix = $pathSuffix;
        return $this;
    }

    /**
     * @return RouteNode
     */
    public function getRouteNode()
    {
        return $this->routeNode;
    }

    /**
     * @param RouteNode $routeNode
     * @return RouteAction
     */
    public function setRouteNode($routeNode)
    {
        $this->routeNode = $routeNode;
        return $this;
    }


    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }


    /**
     * @return string
     */
    public function getMethod()
    {
        return self::$possibleActions[$this->action]['method'];
    }

    /**
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RouteAction
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * @param \Closure $closure
     * @return RouteAction
     */
    public function setClosure($closure)
    {
        $this->closure = $closure;
        return $this;
    }

    /**
     * @return string
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * @param string $uses
     * @return RouteAction
     */
    public function setUses($uses)
    {
        $this->uses = $uses;
        return $this;
    }
    
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

            // A full route name always starts with the language-key.
            $fullRouteName = $language;

            // Then we append the full name of the route-node.
            if (strlen($this->routeNode->getId())>0) {
                $fullRouteName .=  '.' . $this->routeNode->getId();
            }

            // Append the suffix for this action, if defined.
            if (isset(RouteAction::$possibleActions[$this->action]['suffix'])) {
                $fullRouteName .= '.' . self::$possibleActions[$this->action]['suffix'];
            }

            // Set the full route name in the action-array.
            $action['as'] = $fullRouteName;

            // Get the path for this route-node and language to register this route with.
            $path = $this->routeNode->getPath($language);

            // Append any configured suffix.
            if (strlen($this->pathSuffix)>0) {
                $path .= $this->pathSuffix;
            }

            // Now register the route with laravel.
            \Route::$method($path, $action);

            // And tell the RouteTree service about this registered route,
            // so he can manage a static list.
            app()[RouteTree::class]->registerPath($path, $this);

        }
        
    }

}