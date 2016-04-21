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
    protected $routeTree = null;

    protected $registeredPaths = [];

    protected $registeredPathsByMethod = [];

    /**
     * RouteTree constructor.
     */
    public function __construct()
    {
        $this->routeTree = new RouteNode("");
    }


    public function setRootNode($nodeData=[]) {
        $this->generateNode("",$nodeData,null);
    }

    public function addNode($nodeName, $nodeData=[], $parentNode = "") {

        // TODO: Find out Parent node

        $this->generateNode($nodeName, $nodeData, );
    }

    public function addNodes($nodes=[], $parentNode = "") {
        foreach ($nodes as $nodeName => $nodeData) {
            $this->addNode($nodeName, $nodeData, $parentNode);
        }
    }

    protected function generateNode($nodeName="", $nodeData=[], $parentNode = null) {

        // Create new RouteNode.
        $routeNode = new RouteNode($nodeName, $parentNode);

        // If no parent-node is stated and the current routeName is an empty string,
        // we set this routeNode as the root-node.
        if (is_null($parentNode) && ($nodeName === '')) {
            $this->routeTree = $routeNode;
        }

        // Set specific paths, if configured.
        if (isset($nodeData['path'])) {
            $routeNode->setPaths($nodeData['path']);
        }

        // Add middleware.
        if (isset($nodeData['middleware'])) {
            $routeNode->addMiddlewareFromArray($nodeData['middleware']);
        }

        // Add actions.
        foreach (RouteAction::$possibleActions as $action => $actionInfo) {
            if (isset($nodeData[$action])) {

                $actionData = $nodeData[$action];

                // Create RouteAction Object
                $routeAction = new RouteAction($action, $routeNode);

                if (isset($actionData['closure'])) {
                    $routeAction->setClosure($actionData['closure']);
                }
                else if (isset($actionData['uses'])) {
                    $routeAction->setUses($actionData['uses']);
                }

                $routeNode->addAction($routeAction);
            }
        }

        // Set resource-info, if configured.
        if (isset($nodeData['resource'])) {

            // Create and set RouteResourceObject.
            $routeNode->setResource(
                (new RouteResource($nodeData['resource'],$routeNode))
                    ->setController($nodeData['resourceController'])
                    ->setActions($nodeData['resourceActions'])
            );
        }

        // Process Children, if present.
        if (isset($nodeData['children']) && (count($nodeData['children'])>0)) {
            $this->processRoutesFromArray($nodeData['children'], $routeNode);
        }
    }

    public function generateAllRoutes() {
        $this->routeTree->generateRoutesOfNodeAndChildNodes();
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
}