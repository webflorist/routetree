<?php

namespace Webflorist\RouteTree\Domain\Traits;

/**
 * Trait CanHaveParameterRegex
 *
 * This trait provides RouteNodes and RouteActions
 * with functionality to set Regular Expression Constraints.
 * (see https://laravel.com/docs/master/routing#parameters-regular-expression-constraints)
 *
 * @package Webflorist\RouteTree
 */
trait CanHaveParameterRegex
{

    /**
     * Regular expression requirements of route parameters.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * Set a regular expression requirement on the RouteNode.
     *
     * @param array|string $name
     * @param string|null $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param array|string $name
     * @param string $expression
     * @return array
     */
    protected function parseWhere($name, $expression)
    {
        return is_array($name) ? $name : [$name => $expression];
    }

}