<?php

namespace Webflorist\RouteTree\Domain;


use Webflorist\RouteTree\Exceptions\NoRouteParameterModelException;
use Webflorist\RouteTree\Interfaces\RouteParameterModelContract;
use Webflorist\RouteTree\RouteTree;

/**
 * Parameter for RouteNodes.
 *
 * Class RouteParameter
 * @package Webflorist\RouteTree
 *
 */
class RouteParameter
{
    /**
     * The parameter name.
     *
     * @var string
     */
    private $name;

    /**
     * The RouteNode this parameter belongs to.
     *
     * @var RouteNode
     */
    private $routeNode;

    /**
     * Class of a model bound to this route parameter.
     *
     * @var string
     */
    private $model;

    /**
     * Specific values for this RouteParameter.
     * (Either one-dimensional for all languages
     * or multi-dimensional per language).
     *
     * @var array
     */
    private $values;

    /**
     * RouteParameter constructor.
     *
     * @param string $parameter
     * @param RouteNode $routeNode
     */
    public function __construct(string $parameter, RouteNode $routeNode)
    {
        $this->routeNode = $routeNode;
        $this->name = $parameter;
    }

    public function model(string $model)
    {
        if (!isset(class_implements($model)[RouteParameterModelContract::class])) {
            throw new NoRouteParameterModelException("Model '$model' does not implement 'RouteParameterModel'");
        }
        $this->model = $model;
    }

    public function values(array $values)
    {
        $this->values = $values;
    }

    public function getValues(?string $locale=null, ?array $parameters=null)
    {
        RouteTree::establishLocale($locale);
        RouteTree::establishRouteParameters($parameters);

        if (!is_null($this->values)) {
            // if $this->values is multidimensional, we assume it's localised
            if (is_array(array_values($this->values)[0])) {
                return $this->values[$locale];
            }
            return $this->values;
        }

        if (!is_null($this->model)) {
            return $this->model::getRouteParameterValues($locale, $parameters);
        }

        return [];

    }

    public function hasValues(string $locale, array $parameters=null)
    {
        return count($this->getValues($locale, $parameters)) > 0;
    }

    public function getName()
    {
        return $this->name;
    }


}
