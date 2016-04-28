<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 20.04.2016
 * Time: 16:39
 */

namespace Nicat\RouteTree;

class RouteTree {

    /**
     * @var RouteNode|null
     */
    protected $rootNode = null;

    protected $registeredPaths = [];

    protected $registeredPathsByMethod = [];

    /**
     * @var RouteNode|null
     */
    protected $currentNode = null;

    /**
     * @var NodeGenerator|null
     */
    protected $nodeGenerator = null;

    /**
     * RouteTree constructor.
     */
    public function __construct()
    {
        $this->rootNode = new RouteNode("");
        
        $this->nodeGenerator = new NodeGenerator($this);
    }


    /**
     * @param RouteNode|array $nodeData
     */
    public function setRootNode($nodeData=[]) {

        if (is_a($nodeData,RouteNode::class)) {
            $this->rootNode = $nodeData;
        }
        else if (is_array($nodeData)) {
            $this->rootNode = $this->nodeGenerator->generateNode("",null,$nodeData);
        }
    }


    public function getRootNode() {
        return $this->rootNode;
    }


    public function getCurrentNode() {
        return $this->currentNode;
    }


    public function setCurrentNode(RouteNode $routeNode) {
        $this->currentNode = $routeNode;
    }

    public function addNode($nodeName, $nodeData=[], $parentNodeId = "") {

        $this->nodeGenerator->generateNode(
            $nodeName,
            $this->getOrGenerateNode($parentNodeId),
            $nodeData
        );
    }

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

                // Otherwise, we
                return new RouteNode(
                    substr($nodeId,$lastSlashPosition+1),
                    $this->getOrGenerateNode(substr($nodeId,0,$lastSlashPosition))
                );

            }

        }
    }

    public function doesNodeExist($nodeId='') {
        if (is_a($this->getNode($nodeId), RouteNode::class)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getNode($nodeId='') {

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

    public function generateAllRoutes() {
        $this->rootNode->generateRoutesOfNodeAndChildNodes();
    }

    public function registerPath($path='', RouteAction $routeAction) {

        // Add path to overall list of registered paths.
        $this->registeredPaths[$path][$routeAction->getAction()] = $routeAction;

        // Add path to registered paths sorted by method.
        $this->registeredPathsByMethod[$routeAction->getMethod()][$path][$routeAction->getAction()] = $routeAction;

    }

    /**
     * @return array
     */
    public function getRegisteredPaths()
    {
        return $this->registeredPaths;
    }

    /**
     * @return array
     */
    public function getRegisteredPathsByMethod($method)
    {
        return $this->registeredPathsByMethod[strtolower($method)];
    }

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
    

}