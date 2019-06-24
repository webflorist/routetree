<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 20.04.2016
 * Time: 16:39
 */

namespace Webflorist\RouteTree;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Webflorist\RouteTree\Exceptions\NodeNotFoundException;
use Webflorist\RouteTree\Exceptions\RouteNameAlreadyRegisteredException;

class RouteTree {

    /**
     * Root-node of the route-tree (= the whole route-tree).
     *
     * @var RouteNode|null
     */
    protected $rootNode = null;

    /**
     * Laravel-Collection of all Routes registered with the route-tree.
     *
     * @var Collection
     */
    protected $registeredRoutes;

    /**
     * Currently active RouteAction.
     *
     * @var RouteAction|null
     */
    protected $currentAction = null;

    /**
     * Instance of the node-generator used to generate nodes out of an array.
     *
     * @var NodeGenerator|null
     */
    protected $nodeGenerator = null;

    /**
     * Gets set to true, if all routes have been successfully generated.
     *
     * @var bool
     */
    protected $routesGenerated = false;

    /**
     * RouteTree constructor.
     */
    public function __construct()
    {
        // Create an empty root-node.
        $this->rootNode = new RouteNode("");

        // Create instance of the node-generator.
        $this->nodeGenerator = new NodeGenerator($this);

        // Create instance of the registeredRoutes-collection.
        $this->registeredRoutes = new Collection();
    }

    /**
     * Set the root-node.
     *
     * @param RouteNode|array $nodeData Can be either a RouteNode-object or an array of node-data.
     */
    public function setRootNode($nodeData) {

        if (is_a($nodeData,RouteNode::class)) {
            $this->rootNode = $nodeData;
        }
        else if (is_array($nodeData)) {
            $this->rootNode = $this->nodeGenerator->generateNode("",null,$nodeData);
        }
    }

    /**
     * Get the root-node (= the whole route-tree).
     *
     * @return RouteNode|null
     */
    public function getRootNode() {
        return $this->rootNode;
    }

    /**
     * Get the currently active node.
     *
     * @return RouteNode|null
     */
    public function getCurrentNode() {
        if ($this->pageNotFound()) {
            return  $this->getRootNode();
        }
        return $this->currentAction->getRouteNode();
    }

    /**
     * Get the currently active action.
     *
     * @return RouteAction|null
     */
    public function getCurrentAction() {
        if ($this->pageNotFound()) {
            return  $this->getRootNode()->getAction('index');
        }
        return $this->currentAction;
    }

    /**
     * Sets the currently active RouteAction.
     *
     * @param RouteAction $routeAction
     */
    public function setCurrentAction(RouteAction $routeAction) {
        $this->currentAction = $routeAction;
    }

    /**
     * Adds a new node to the route-tree.
     *
     * @param string $nodeName Name of this node.
     * @param array $nodeData Node-data structured as array.
     * @param string $parentNodeId Node-ID of the parent node. If omitted, the root-node is used.
     */
    public function addNode($nodeName, $nodeData=[], $parentNodeId = "") {

        $this->nodeGenerator->generateNode(
            $nodeName,
            $this->getOrGenerateNode($parentNodeId),
            $nodeData
        );
    }

    /**
     * Adds an array of nodes to the route-tree.
     *
     * @param array $nodes Multi-dimensional array, whose key is the node-name and whose values are the node-data.
     * @param string $parentNodeId Node-ID of the parent node. If omitted, the root-node is used.
     */
    public function addNodes($nodes=[], $parentNodeId="") {

        $parentNode = $this->getOrGenerateNode($parentNodeId);

        foreach ($nodes as $nodeName => $nodeData) {
            $this->nodeGenerator->generateNode(
                $nodeName,
                $parentNode,
                $nodeData
            );
        }
    }

    /**
     * Gets a node via it's id.
     * If it does not exist, it creates the node and it's missing parents.
     *
     * @param string $nodeId
     * @return bool|RouteNode|null
     */
    protected function getOrGenerateNode($nodeId='') {

        // Check, if $parentNodePath exists
        if ($this->doesNodeExist($nodeId)) {

            // If it exists, we return it.
            return $this->getNode($nodeId);
        }
        else {

            // If it does not exist, we check for the previous hierarchical level.
            $lastSlashPosition = strrpos($nodeId,'.');

            // In case, we are already at level 1, the parent node must be the root-node,
            // so we create the node for $path with the root-node as it's parent and return it.
            if ($lastSlashPosition === false) {
                return new RouteNode($nodeId, $this->rootNode);
            }
            else {

                // Otherwise, we create the node using getOrGenerateNode() again for it's parent.
                return new RouteNode(
                    substr($nodeId,$lastSlashPosition+1),
                    $this->getOrGenerateNode(substr($nodeId,0,$lastSlashPosition))
                );

            }

        }
    }

    /**
     * Checks, if a node exists.
     *
     * @param string $nodeId
     * @return bool
     */
    public function doesNodeExist($nodeId) {
        try {
            $this->getNode($nodeId);
        }
        catch (NodeNotFoundException $exception) {
            return false;
        }
        return true;
    }

    /**
     * Get's the RouteNode via it's ID.
     *
     * @param string $nodeId
     * @return RouteNode
     * @throws NodeNotFoundException
     */
    public function getNode($nodeId) {

        // If path is an empty string or null, we return the root-node.
        if ($nodeId === "" || $nodeId === null) {
            return $this->rootNode;
        }

        // Otherwise we explode the path into it's segments.
        $pathSegments = explode('.',$nodeId);

        // We start crawling beginning with the root-node.
        $crawlNode = $this->rootNode;

        // Crawl each path-segment...
        foreach ($pathSegments as $segment) {

            // Get the node for the current segment..
            if ($crawlNode->hasChildNode($segment)) {

                // If it was found, we set it as the new $crawlNode.
                $crawlNode = $crawlNode->getChildNode($segment);
            }
            else {

                // If it was not found, it is clear, that no node exists for $path.
                // So we return false.
                throw new NodeNotFoundException("Node with ID '".$nodeId."' could not be found.");
            }
        }

        // If a node for the last path-segment was found, we return it.
        return $crawlNode;
    }

    /**
     * Generates all routes of the route-tree.
     */
    public function generateAllRoutes() {
        if (!$this->routesGenerated) {
            $this->rootNode->generateRoutesOfNodeAndChildNodes();
        }
        $this->routesGenerated = true;
    }

    /**
     * Register a Laravel-route and it's action with the route-tree.
     * This is used within the generateRoutes()-method of a RouteAction-object.
     *
     * @param Route $route
     * @param RouteAction $routeAction
     * @param $language
     * @throws RouteNameAlreadyRegisteredException
     */
    public function registerRoute(Route $route, RouteAction $routeAction, $language) {

        $key = strtoupper($routeAction->getMethod()) . $route->getName();

        if ($this->registeredRoutes->has($key)) {
            throw new RouteNameAlreadyRegisteredException('Route with key "'.$key.'" already registered.');
        }

        $this->registeredRoutes->put(
            $key,
            [
                'route' => $route,
                'route_action' => $routeAction,
                'language' => $language,
                'method' => $routeAction->getMethod(),
                'path' => $route->uri(),
                'route_name' => $route->getName()
            ]
        );

    }

    /**
     * Get collection of Routes registered with the RouteTree.
     *
     * @return Collection
     */
    public function getRegisteredRoutes()
    {
        return $this->registeredRoutes;
    }

    /**
     * Get a Collection of Routes registered with the RouteTree with a specific method
     * (and optionally limited to a certain language).
     *
     * @param string $method
     * @param null|string $language
     * @return Collection
     */
    public function getRegisteredRoutesByMethod($method, $language=null)
    {
        $filteredRoutes = $this->registeredRoutes->where('method', strtolower($method));

        if (!is_null($language)) {
            $filteredRoutes = $filteredRoutes->where('language', $language);
        }

        return $filteredRoutes->sortBy('path');
    }

    /**
     * Tries to get a node using it's full route-name.
     *
     * @param string $routeName
     * @param null $method
     * @return bool|RouteNode|null
     */
    public function getNodeByRouteName($routeName, $method=null) {

        $routeAction = $this->getActionByRouteName($routeName, $method);

        if ($routeAction !== false) {
            return $routeAction->getRouteNode();
        }

        // If no node was found, we return false.
        return false;

    }

    /**
     * Tries to retrieve the correct RouteAction corresponding to a certain Laravel-Route-Name.
     *
     * @param string $routeName
     * @param null $method
     * @return bool|RouteAction|null
     */
    public function getActionByRouteName($routeName, $method=null) {

        // Since $routeName may be used for multiple methods,
        // we search for a matching registeredRoute in the following order.
        $matchMethods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        // If a method was specifically stated, we just search for that one.
        if (!is_null($method)) {
            $matchMethods = [strtoupper($method)];
        }

        foreach ($matchMethods as $matchMethod) {
            $matchKey = $matchMethod.$routeName;
            if ($this->registeredRoutes->has($matchKey)) {
                return $this->registeredRoutes->get($matchKey)['route_action'];
            }
        }

        // If no action was found, we return false.
        return false;

    }

    /**
     * Tries to retrieve the correct RouteAction corresponding to a certain Laravel-Route.
     *
     * @param Route $route
     * @return bool|RouteAction|null
     */
    public function getActionByRoute(Route $route) {

        $filteredRoutes = $this->registeredRoutes->where('route', $route);

        if ($filteredRoutes->count() > 0) {
            return $filteredRoutes->first()['route_action'];
        }

        // If no action was found, we return false.
        return false;

    }

    /**
     * Fills $locale with the current locale, if it is null.
     *
     * @param $locale
     */
    public static function establishLocale(&$locale) {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }
    }

    /**
     * Fills $parameters with the current route-parameters, if it is null.
     *
     * @param $parameters
     * @return array
     */
    public static function establishRouteParameters(&$parameters) {
        if (is_null($parameters) and  !is_null(\Route::current())) {
            return $parameters = \Route::current()->parameters();
        }

        return $parameters = [];
    }

    /**
     * Check if we have a currentAction else we have possible a 404
     *
     * @return bool
     */
    public function pageNotFound()
    {
        return $this->currentAction ? false : true;
    }


}
