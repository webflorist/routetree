<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 21.04.2016
 * Time: 14:40
 */

namespace Nicat\RouteTree;


class RouteResource
{

    /**
     * @var string
     */
    protected $resource = null;

    /**
     * @var string
     */
    protected $controller = null;

    /**
     * @var RouteAction[]
     */
    protected $actions = [];

    /**
     * @var RouteNode
     */
    protected $routeNode = null;

    /**
     * RouteResource constructor.
     * @param string $resource
     * @param RouteNode $routeNode
     */
    public function __construct($resource, RouteNode $routeNode)
    {
        $this->setResource($resource);
        $this->setRouteNode($routeNode);

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
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     * @return RouteResource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @return RouteResource
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     * @return RouteResource
     */
    public function setActions($actions=[])
    {
        foreach ($actions as $key => $action) {

            // Create the RouteAction Object and set controller and method (=action).
            $routeAction = new RouteAction($action, $this->routeNode);
            $routeAction->setUses($this->controller.'@'.$action);

            // If action is show|edit|update|destroy, we we must add the resource-parameter to the path.
            if (($action == 'show') || ($action == 'edit') || ($action == 'update') || ($action == 'destroy')) {
                $pathSuffix = '/{' . $this->resource . '}';

                // For edit we also add an "edit" to the path
                if ($action == 'edit') {
                    $pathSuffix .= '/edit';
                }

                // We set the pathSuffix within the RouteAction object.
                $routeAction->setPathSuffix($pathSuffix);
            }

            // Add this RouteAction to $this->actions.
            array_push($this->actions,$routeAction);
        }
        return $this;
    }

    public function generateRoutes() {
        if (count($this->actions)>0) {
            foreach ($this->actions as $key => $routeAction) {
                $routeAction->generateRoutes();
            }
        }
    }



}