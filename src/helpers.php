<?php

use Webflorist\RouteTree\Exceptions\ActionNotFoundException;
use Webflorist\RouteTree\Exceptions\NodeNotFoundException;
use Webflorist\RouteTree\Exceptions\UrlParametersMissingException;
use Webflorist\RouteTree\RouteTree;

if (! function_exists('route_tree')) {
    /**
     * Gets the RouteTree singleton from Laravel's service-container
     *
     * @return \Webflorist\RouteTree\RouteTree
     */
    function route_tree()
    {
        return app(RouteTree::class);
    }
}


if ( ! function_exists('route_node_url()')) {
    /**
     * Generate an URL to the action of a route-node.
     *
     * @param string $nodeId The node-id for which this url is generated (default=current node).
     * @param string $action The node-action for which this url is generated (default='index|get').
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * @param string $language The language this url should be generated for (default=current locale).
     * @param bool $absolute Create absolute paths instead of relative paths (default=true/configurable).
     * @return string
     * @throws ActionNotFoundException
     * @throws NodeNotFoundException
     * @throws UrlParametersMissingException
     */
    function route_node_url($nodeId=null, $action=null, $parameters = null, $language=null, $absolute=null)
    {
        if (is_null($nodeId)) {
            $node = route_tree()->getCurrentNode();
        }
        else {
            $node = route_tree()->getNode($nodeId);
        }

        if (is_null($action)) {
            if ($node->hasAction('index')) {
                $action = 'index';
            }
            else if ($node->hasAction('get')) {
                $action = 'get';
            }
        }

        return $node->getUrlByAction($action, $parameters, $language, $absolute);
    }
}

if ( ! function_exists('route_node_id()')) {
    /**
     * Get the node-id of the current route.
     *
     * @return string
     */
    function route_node_id()
    {
        return route_tree()->getCurrentNode()->getId();
    }
}

if (! function_exists('trans_by_route')) {

    /**
     * Translate the given message and work with current route.
     *
     * @param string $id
     * @param bool $useParentNode
     * @param string $nodeId
     * @param array $parameters
     * @param string $locale
     * @return string
     */
    function trans_by_route($id = null, $useParentNode = false, $nodeId = '', $parameters = [], $locale = null)
    {

        if(empty($nodeId)) {
            $routeNode = route_tree()->getCurrentNode();
        }
        else {
            $routeNode = route_tree()->getNode($nodeId);
        }

        if ($useParentNode) {
            $routeNode = $routeNode->getParentNode();
        }

        $id = $routeNode->getContentLangFile() . '.' . $id;

        return trans($id, $parameters, $locale);
    }
}