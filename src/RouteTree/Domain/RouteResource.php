<?php

namespace Webflorist\RouteTree\Domain;

use Closure;
use Illuminate\Support\Facades\Lang;
use Webflorist\RouteTree\Exceptions\NodeAlreadyHasChildWithSameNameException;
use Webflorist\RouteTree\Exceptions\NodeNotFoundException;
use Webflorist\RouteTree\Exceptions\NoValidModelException;
use Webflorist\RouteTree\RouteTree;

/**
 * Class RouteResource
 *
 * This class manages data for
 * resourceful RouteNodes.
 *
 * (see https://laravel.com/docs/master/controllers#restful-partial-resource-routes)
 *
 * @package Webflorist\RouteTree
 */
class RouteResource
{

    /**
     * The RouteNode this resource belongs to.
     *
     * @var RouteNode
     */
    protected $routeNode = null;

    /**
     * The controller-method to be used for actions of this resource.
     *
     * @var string
     */
    protected $controller = null;

    /**
     * The name of the resource / route parameter.
     *
     * @var string
     */
    private $name;

    /**
     * RouteAction constructor.
     *
     * @param string $name
     * @param $controller
     * @param RouteNode $routeNode
     */
    public function __construct(string $name, $controller, $routeNode)
    {
        $this->name = $name;
        $this->controller = $controller;
        $this->routeNode = $routeNode;
        $this->routeNode->parameter($name, false);

        $this->setupActions();

        return $this;
    }

    /**
     * Get the name of this resource.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setup all resourceful actions.
     */
    private function setupActions()
    {
        $controller = $this->controller;
        $paramSegment = '{' . $this->name . '}';

        $this->routeNode->get("$controller@index", 'index');
        $this->routeNode->get("$controller@create", 'create')->segment($this->getCreateActionSegments());
        $this->routeNode->post("$controller@store", 'store');
        $this->routeNode->get("$controller@show", 'show')->segment($paramSegment);
        $this->routeNode->get("$controller@edit", 'edit')->segment($this->getEditActionSegments());
        $this->routeNode->put("$controller@update", 'update')->segment($paramSegment);
        $this->routeNode->delete("$controller@destroy", 'destroy')->segment($paramSegment);
    }

    /**
     * Creates the path-segments for create actions
     * (defaults to '/create' in english).
     *
     * @return LanguageMapping
     */
    private function getCreateActionSegments()
    {
        $segments = LanguageMapping::create();
        foreach ($this->routeNode->getLocales() as $locale) {
            $translationKey = 'Webflorist-RouteTree::routetree.createPathSegment';
            $translationLocale = Lang::hasForLocale($translationKey, $locale) ? $locale : 'en';
            $segments->set($locale, __($translationKey, [], $translationLocale));
        }
        return $segments;
    }

    /**
     * Creates the path-segments for edit actions
     * (defaults to '/{resource}/edit' in english).
     *
     * @return LanguageMapping
     */
    private function getEditActionSegments()
    {
        $paramSegment = '{' . $this->name . '}';
        $segments = LanguageMapping::create();
        foreach ($this->routeNode->getLocales() as $locale) {
            $translationKey = 'Webflorist-RouteTree::routetree.editPathSegment';
            $translationLocale = Lang::hasForLocale($translationKey, $locale) ? $locale : 'en';
            $segments->set($locale, $paramSegment . '/' . __($translationKey, [], $translationLocale));
        }
        return $segments;
    }

    /**
     * Register only the stated actions for this RouteResource.
     *
     * @param array $actionsOnly
     * @return $this
     */
    public function only(array $actionsOnly)
    {
        foreach ($this->routeNode->getActions() as $routeAction) {
            if (array_search($routeAction->getName(), $actionsOnly) === false) {
                $this->routeNode->removeAction($routeAction->getName());
            }
        }
        return $this;
    }

    /**
     * Register all resource actions except the stated ones.
     *
     * @param array $actionsExcept
     * @return $this
     */
    public function except(array $actionsExcept)
    {
        foreach ($this->routeNode->getActions() as $routeAction) {
            if (array_search($routeAction->getName(), $actionsExcept) !== false) {
                $this->routeNode->removeAction($routeAction->getName());
            }
        }
        return $this;
    }

    /**
     * Attaches an Eloquent Model to the RouteParameter
     * to use for various functionality.
     *
     * @param string $class
     * @return $this
     * @throws NoValidModelException
     */
    public function model(string $class)
    {
        $this->routeNode->parameter->model($class);
        return $this;
    }

    /**
     * Create a new resource-child-node.
     *
     * Resource children inherit the path of the parent's show-action.
     * (e.g. /parent-which-is-resource/{resource}/resource-child)
     *
     * @param string $name
     * @param Closure $callback
     * @return RouteNode
     * @throws NodeNotFoundException
     * @throws NodeAlreadyHasChildWithSameNameException
     */
    public function child(string $name, Closure $callback)
    {
        $child = $this->routeNode->child($name, $callback);
        $child->isResourceChild = true;
        return $child;
    }

    /**
     * Retrieve a default page title for a resource action.
     *
     * @param string $actionName
     * @param array|null $parameters
     * @param string|null $locale
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     * @throws \Webflorist\RouteTree\Exceptions\ActionNotFoundException
     */
    public function getActionTitle(string $actionName, ?array $parameters = null, ?string $locale = null)
    {
        RouteTree::establishLocale($locale);
        $resourceTitle = $this->routeNode->getTitle($parameters, $locale, false);
        $resourceItem = $this->getResourceItem($parameters, $locale);
        switch ($actionName) {
            case 'create':
                return trans('Webflorist-RouteTree::routetree.createTitle', ['resource' => $resourceTitle], $locale);
            case 'show':
                return "$resourceTitle: $resourceItem";
            case 'edit':
                return trans('Webflorist-RouteTree::routetree.editTitle', ['resource' => $resourceTitle, 'item' => $resourceItem], $locale);
            default:
                return $resourceTitle;
        }
    }

    /**
     * Retrieve a default page navigation-title for a resource action.
     *
     * @param string $actionName
     * @param array|null $parameters
     * @param string|null $locale
     * @return array|\Illuminate\Contracts\Translation\Translator|mixed|string|null
     * @throws \Webflorist\RouteTree\Exceptions\ActionNotFoundException
     */
    public function getActionNavTitle(string $actionName, ?array $parameters = null, ?string $locale = null)
    {
        switch ($actionName) {
            case 'create':
                return trans('Webflorist-RouteTree::routetree.createNavTitle', [], $locale);
            case 'show':
                return $resourceItem = $this->getResourceItem($parameters, $locale);
            case 'edit':
                return trans('Webflorist-RouteTree::routetree.editNavTitle', [], $locale);
            default:
                return $this->routeNode->getNavTitle($parameters, $locale, false);
        }
    }

    /**
     * Retrieves the current route-key for this resource -
     * either from $parameters or from the currently active URL.
     *
     * @param array|null $parameters
     * @param string|null $locale
     * @return mixed|null
     */
    protected function getResourceItem(?array $parameters, ?string $locale)
    {
        if (isset($parameters[$this->name])) {
            $resourceItem = $parameters[$this->name];
        } else {
            $resourceItem = $this->routeNode->parameter->getActiveRouteKey($locale);
        }
        return $resourceItem;
    }

}