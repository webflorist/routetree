<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 28.04.2016
 * Time: 10:06
 */

namespace Nicat\RouteTree;


class NodeGenerator
{

    /**
     * @var RouteTree|null
     */
    protected $nodeTree = null;

    /**
     * NodeGenerator constructor.
     * @param RouteTree $nodeTree
     */
    public function __construct($nodeTree)
    {
        $this->nodeTree = $nodeTree;
    }

    public function generateNode($nodeName="", $parentNode = null, $nodeData=[]) {

        // Create new RouteNode.
        $routeNode = new RouteNode($nodeName, $parentNode);

        foreach ($nodeData as $key => $value) {

            switch($key) {
                case 'path':
                    $routeNode->setPaths($value);
                    break;
                case 'middleware':
                    $routeNode->addMiddlewareFromArray($value);
                    break;
                case 'namespace':
                    $routeNode->setNamespace($value);
                    break;
                case 'pageTitle':
                    $routeNode->setPageTitle($value);
                    break;
                case 'inheritPath':
                    $routeNode->setInheritPath($value);
                    break;
                case 'index':
                case 'create':
                case 'store':
                case 'show':
                case 'edit':
                case 'update':
                case 'destroy':
                case 'get':
                case 'post':
                    $this->addActionToNode($routeNode, $key, $value);
                    break;
                case 'values':
                    $routeNode->setValues($value);
                    break;
                case 'resource':
                    $this->processResource($value, $routeNode);
                    break;
                default:
                    $routeNode->setData($key, $value);

            }

        }

        // Process Children, if present.
        $this->processChildren($nodeData, $routeNode);

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

    /**
     * @param $resourceData
     * @param RouteNode $routeNode
     * @param $action
     */
    private function addResourceAction($resourceData, $routeNode, $action)
    {
        // Create new RouteAction.
        $routeAction = new RouteAction($action);

        // Set controller.
        $routeAction->setUses($resourceData['controller'] . '@' . $action);

        // Set the path-suffix for a single resource (used by actions show|edit|update|destroy.
        $resourcePathSuffix = '/{' . $resourceData['name'] . '}';

        // Add corresponding suffix.
        switch ($action) {
            case 'create':
                $routeAction->setPathSuffix('/create');
                break;
            case 'show':
                $routeAction->setPathSuffix($resourcePathSuffix);
                break;
            case 'edit':
                $routeAction->setPathSuffix($resourcePathSuffix . '/edit');
                break;
            case 'update':
                $routeAction->setPathSuffix($resourcePathSuffix);
                break;
            case 'destroy':
                $routeAction->setPathSuffix($resourcePathSuffix);
                break;
        }

        $routeNode->addAction($routeAction);
    }

    /**
     * @param $resourceData
     * @param RouteNode $routeNode
     * @return mixed
     */
    private function processResource($resourceData, $routeNode)
    {
        foreach ($this->establishResourceActionList($resourceData) as $action) {
            $this->addResourceAction($resourceData, $routeNode, $action);
        }
    }

    /**
     * @param $nodeData
     * @param $routeNode
     */
    private function processChildren($nodeData, $routeNode)
    {
        if (isset($nodeData['children']) && (count($nodeData['children']) > 0)) {
            foreach ($nodeData['children'] as $childName => $childData) {
                $this->generateNode($childName, $routeNode, $childData);
            }
        }
    }

    /**
     * @param $resourceData
     * @return array
     */
    private function establishResourceActionList($resourceData)
    {

        // Normally we assume, all possible actions should be set.
        $resourceActions = [
            'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'
        ];

        // Set only those actions listed in $nodeData['resource']['only'] (if set).
        if (isset($resourceData['only'])) {
            $resourceActions = $resourceData['only'];
        }

        // Unset those actions listed in $nodeData['resource']['except'] (if set).
        if (isset($resourceData['except'])) {
            foreach ($resourceData['except'] as $resource) {
                if (($key = array_search($resource, $resourceActions)) !== false) {
                    unset($resourceActions[$key]);
                }
            }
        }

        return $resourceActions;
    }

}