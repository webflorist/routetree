<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 20.04.2016
 * Time: 16:39
 */

namespace Nicat\RouteTree;

use Illuminate\Routing\Route;

class RouteTree {

    /**
     * Root-node of the route-tree (= the whole route-tree).
     *
     * @var RouteNode|null
     */
    protected $rootNode = null;

    /**
     * Static list of paths registered with the route-tree.
     *
     * @var array
     */
    protected $registeredPaths = [];

    /**
     * Static list of paths registered with the route-tree sorted by method.
     *
     * @var array
     */
    protected $registeredPathsByMethod = [];

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
     * RouteTree constructor.
     */
    public function __construct()
    {
        // Create an empty root-node.
        $this->rootNode = new RouteNode("");

        // Create instance of the node-generator.
        $this->nodeGenerator = new NodeGenerator($this);
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
        return $this->currentAction->getRouteNode();
    }

    /**
     * Get the currently active action.
     *
     * @return RouteAction|null
     */
    public function getCurrentAction() {
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
        if (is_a($this->getNode($nodeId), RouteNode::class)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Get's the RouteNode via it's ID.
     *
     * @param string $nodeId
     * @return bool|RouteNode|null
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
                return false;
            }
        }

        // If a node for the last path-segment was found, we return it.
        return $crawlNode;
    }

    /**
     * Generates all routes of the route-tree.
     */
    public function generateAllRoutes() {
        $this->rootNode->generateRoutesOfNodeAndChildNodes();
    }

    /**
     * Register a path and it's action with the route-tree.
     * This is used within the generateRoutes()-method of a RouteAction-object.
     *
     * @param string $path
     * @param RouteAction $routeAction
     */
    public function registerPath($path='', RouteAction $routeAction) {

        // Add path to overall list of registered paths.
        $this->registeredPaths[$path][$routeAction->getAction()] = $routeAction;

        // Add path to registered paths sorted by method.
        $this->registeredPathsByMethod[$routeAction->getMethod()][$path][$routeAction->getAction()] = $routeAction;

    }

    /**
     * Get an array of paths registered with the RouteTree.
     *
     * @return array
     */
    public function getRegisteredPaths()
    {
        ksort($this->registeredPaths);
        return $this->registeredPaths;
    }

    /**
     * Get an array of paths registered with the RouteTree with a specific method.
     *
     * @param $method
     * @return array
     */
    public function getRegisteredPathsByMethod($method)
    {
        ksort($this->registeredPathsByMethod[strtolower($method)]);
        return $this->registeredPathsByMethod[strtolower($method)];
    }

    /**
     * Tries to get a node using it's full route-name.
     *
     * @param string $routeName
     * @return bool|RouteNode|null
     */
    public function getNodeByRouteName($routeName='') {

        // Split route name to array.
        $routeSegments = explode('.', $routeName);
        $lengthOfRoute = count($routeSegments);

        // Remove the language prefix from $routeSegments.
        if (array_key_exists($routeSegments[0], config('app.locales'))) {
            array_shift( $routeSegments  );
            $lengthOfRoute--;
        }

        // Let's see, if such a node exists.
        if ($this->doesNodeExist(implode('.' , $routeSegments))) {
            return $this->getNode(implode('.' , $routeSegments));
        }

        // If not, we try to remove the action suffix, and again try to get the node
        if (preg_match('/(index|create|store|show|edit|update|destroy|get|post)/', $routeSegments[$lengthOfRoute -1])) {
            array_pop( $routeSegments );

            if ($this->doesNodeExist(implode('.' , $routeSegments))) {
                return $this->getNode(implode('.' , $routeSegments));
            }
        }

        // If no node was found, we return false.
        return false;

    }

    /**
     * Tries to retrieve the correct RouteAction corresponding to a certain HTTP-method from a stated Laravel-route.
     *
     * @param string $method
     * @param Route $route
     * @return bool|RouteAction|null
     */
    public function getActionByMethodAndRoute($method='get', Route $route) {

        $method = strtolower($method);
        
        if (isset($this->registeredPathsByMethod[$method]) && isset($this->registeredPathsByMethod[$method][$route->getUri()])) {
            return current($this->registeredPathsByMethod[$method][$route->getUri()]);
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
     */
    public static function establishRouteParameters(&$parameters) {
        if (is_null($parameters)) {
            $parameters = \Route::current()->parameters();
        }
    }
    

}