<?php
/**
 * Created by PhpStorm.
 * User: GeraldB
 * Date: 06.04.2017
 * Time: 13:51
 */

namespace Webflorist\RouteTree\Traits;


trait CanHaveMiddleware
{

    /**
     * Array of middlewares, actions of this node or this action should be registered with.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Gets the middleware-array fot this node.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Adds multiple middleware from an array to this node.
     *
     * @param array $middlewareArray
     */
    public function addMiddlewareFromArray($middlewareArray = []) {
        foreach ($middlewareArray as $middlewareKey => $middlewareData) {
            if (!isset($middlewareData['parameters'])) {
                $middlewareData['parameters'] = [];
            }
            if (!isset($middlewareData['inherit'])) {
                $middlewareData['inherit'] = true;
            }

            if(isset($middlewareData['skip']) and $middlewareData['skip']) {
            	unset($this->middleware[$middlewareKey]);
            }

            if(!isset($middlewareData['skip']) or !$middlewareData['skip']) {
	            $this->addMiddleware($middlewareKey, $middlewareData['parameters'], $middlewareData['inherit']);
            }
        }
    }

    /**
     * Adds a single middleware to this node.
     *
     * @param string $name Name of the middleware.
     * @param array $parameters Parameters the middleware should be called with.
     * @param bool $inherit Should this middleware be inherited to all child-nodes.
     */
    public function addMiddleware($name='', $parameters=[], $inherit=true) {
        $this->middleware[$name] = [
            'parameters' => $parameters,
            'inherit' => $inherit
        ];
    }

}