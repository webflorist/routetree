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

    public $listGenerator;

    protected $registeredPaths = [];

    protected $registeredPathsByMethod = [];

    /**
     * RouteTree constructor.
     */
    public function __construct()
    {
        $this->routeTree = new RouteNode("");

        $this->listGenerator = new ListGenerator($this);
    }


    public function setRootNode($nodeData=[]) {
        $this->generateNode("",null,$nodeData);
    }

    public function addNode($nodeName, $nodeData=[], $parentNodeFullName = "") {

        $this->generateNode(
            $nodeName,
            $this->getOrGenerateNode($parentNodeFullName),
            $nodeData
        );
    }

    public function addNodes($nodes=[], $parentNodePath="") {

        $parentNode = $this->getOrGenerateNode($parentNodePath);

        foreach ($nodes as $nodeName => $nodeData) {
            $this->generateNode(
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
                return new RouteNode($nodeId, $this->routeTree);
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
            return $this->routeTree;
        }

        // Otherwise we explode the path into it's segments.
        $pathSegments = explode('.',$nodeId);

        // We start crawling beginning with the root-node.
        $crawlNode = $this->routeTree;

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

    protected function generateNode($nodeName="", $parentNode = null, $nodeData=[]) {

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

        // Set namespace, if configured.
        if (isset($nodeData['namespace'])) {
            $routeNode->setNamespace($nodeData['namespace']);
        }

        // Add actions.
        foreach (RouteAction::$possibleActions as $action => $actionInfo) {
            if (isset($nodeData[$action])) {
                $this->addActionToNode($routeNode, $action, $nodeData[$action]);
            }
        }

        // Process resource.
        if (isset($nodeData['resource'])) {

            // Normally we assume, all possible actions should be set.
            $resourceActions = [
                'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'
            ];

            // Set only those actions listed in $nodeData['resource']['only'] (if set).
            if (isset($nodeData['resource']['only'])) {
                $resourceActions = $nodeData['resource']['only'];
            }

            // Unset those actions listed in $nodeData['resource']['except'] (if set).
            if (isset($nodeData['resource']['except'])) {
                foreach ($nodeData['resource']['except'] as $resource) {
                    if(($key = array_search($resource, $resourceActions)) !== false) {
                        unset($resourceActions[$key]);
                    }
                }
            }

            // Set the path-suffix for a single resource (used by actions show|edit|update|destroy.
            $resourcePathSuffix = '/{'.$nodeData['resource']['name'].'}';

            // Add each requested action to the current resource node.
            foreach ($resourceActions as $action) {
                switch($action) {
                    case 'index':
                        $routeNode->addAction(
                            (new RouteAction('index'))
                                ->setUses($nodeData['resource']['controller'].'@index')
                        );
                        break;
                    case 'create':
                        $routeNode->addAction(
                            (new RouteAction('create'))
                                ->setUses($nodeData['resource']['controller'].'@create')
                                ->setPathSuffix('/create')
                        );
                        break;
                    case 'store':
                        $routeNode->addAction(
                            (new RouteAction('store'))
                                ->setUses($nodeData['resource']['controller'].'@store')
                        );
                        break;
                    case 'show':
                        $routeNode->addAction(
                            (new RouteAction('show'))
                                ->setUses($nodeData['resource']['controller'].'@show')
                                ->setPathSuffix($resourcePathSuffix)
                        );
                        break;
                    case 'edit':
                        $routeNode->addAction(
                            (new RouteAction('edit'))
                                ->setUses($nodeData['resource']['controller'].'@edit')
                                ->setPathSuffix($resourcePathSuffix.'/edit')
                        );
                        break;
                    case 'update':
                        $routeNode->addAction(
                            (new RouteAction('update'))
                                ->setUses($nodeData['resource']['controller'].'@update')
                                ->setPathSuffix($resourcePathSuffix)
                        );
                        break;
                    case 'destroy':
                        $routeNode->addAction(
                            (new RouteAction('destroy'))
                                ->setUses($nodeData['resource']['controller'].'@destroy')
                                ->setPathSuffix($resourcePathSuffix)
                        );
                        break;
                }
            }
        }

        // Process Children, if present.
        if (isset($nodeData['children']) && (count($nodeData['children'])>0)) {
            foreach ($nodeData['children'] as $childName => $childData) {
                $this->generateNode($childName, $routeNode, $childData);
            }
        }

        return $routeNode;
    }

    /**
     * @param RouteNode $node
     * @param string $actionName
     * @param array $actionData
     */
    protected function addActionToNode(RouteNode $node, $actionName, $actionData) {

        // Create RouteAction Object
        $routeAction = new RouteAction($actionName);

        if (isset($actionData['closure'])) {
            $routeAction->setClosure($actionData['closure']);
        }
        else if (isset($actionData['view'])) {
            $view = $actionData['view'];
            $routeAction->setClosure(function () use($view) {
                return view($view);
            });
        }
        else if (isset($actionData['redirect'])) {
            $redirectTo = $actionData['redirect'];
            $routeAction->setClosure(function () use($redirectTo) {
                return redirect()->route(\App::getLocale().'.'.$redirectTo.'.index');
            });
        }
        else if (isset($actionData['uses'])) {
            $routeAction->setUses($actionData['uses']);
        }

        $node->addAction($routeAction);
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
    
    public function getIdOfCurrentNode() {
        return $this->getNodeIdByRouteName(\Request::route()->getName());
    }

    public function getNodeIdByRouteName($routeName='') {

        // Split route name to array.
        $routeSegments = explode('.', $routeName);
        $lengthOfRoute = count($routeSegments);

        // Remove the language prefix from $routeSegments
        if (array_key_exists($routeSegments[0], config('app.locales'))) {
            array_shift( $routeSegments  );
            $lengthOfRoute--;
        }

        // Remove the action suffix.
        if (preg_match('/(index|create|store|show|edit|update|destroy|get|post)/', $routeSegments[$lengthOfRoute -1])) {
            array_pop( $routeSegments );
            $lengthOfRoute--;
        }

        //check if we have after strip meta values again a valid route
        if($lengthOfRoute < 1) {
            return false;
        }

        return implode('.' , $routeSegments);

    }
    

}