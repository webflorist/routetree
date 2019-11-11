<?php

namespace Webflorist\RouteTree\Domain;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Lang;
use Webflorist\RouteTree\Exceptions\UrlParametersMissingException;
use Webflorist\RouteTree\RouteTree;

class RouteResource
{

    /**
     * The route-node this resource belongs to.
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
     * The name of the resource.
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
     * @throws \Webflorist\RouteTree\Exceptions\NodeAlreadyHasChildWithSameNameException
     * @throws \Webflorist\RouteTree\Exceptions\NodeNotFoundException
     */
    public function __construct(string $name, $controller, $routeNode)
    {
        $this->name = $name;
        $this->controller = $controller;
        $this->routeNode = $routeNode;

        $this->setupActions();

        return $this;
    }

    /**
     * Set the name of this action.
     *
     * @param string $name
     */
    public function name(string $name) {
        $this->name = $name;
    }

    /**
     * Get the name of this action.
     *
     * @return string
     */
    public function getName()
    {
        if (!is_null($this->name)) {
            return $this->name;
        }
        return $this->method;
    }

    /**
     * Returns list of all possible actions, their method, route-name-suffix, parent-action and title-closure.
     *
     * @return array
     */
    public function getActionConfigs()
    {
        return [
            'index' => [
                'method' => 'get',
                'suffix' => 'index',
                'defaultTitle' => function () {
                    return $this->routeNode->getTitle();
                },
                'defaultNavTitle' => function () {
                    return $this->routeNode->getNavTitle();
                }
            ],
            'create' => [
                'method' => 'get',
                'suffix' => 'create',
                'parentAction' => 'index',
                'defaultTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.createTitle', ['resource' => $this->routeNode->getTitle()]);
                },
                'defaultNavTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.createNavTitle');
                }
            ],
            'store' => [
                'method' => 'post',
                'suffix' => 'store'
            ],
            'show' => [
                'method' => 'get',
                'suffix' => 'show',
                'parentAction' => 'index',
                'defaultTitle' => function () {
                    return $this->routeNode->getTitle() . ': ' . $this->routeNode->getActiveValue();
                },
                'defaultNavTitle' => function () {
                    return $this->routeNode->getActiveValue();
                }
            ],
            'edit' => [
                'method' => 'get',
                'suffix' => 'edit',
                'parentAction' => 'show',
                'defaultTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.editTitle', ['item' => $this->routeNode->getActiveValue()]);
                },
                'defaultNavTitle' => function () {
                    return trans('Webflorist-RouteTree::routetree.editNavTitle');
                }
            ],
            'update' => [
                'method' => 'put',
                'suffix' => 'update',
                'parentAction' => 'index'
            ],
            'destroy' => [
                'method' => 'delete',
                'suffix' => 'destroy',
                'parentAction' => 'index'
            ],
            'get' => [
                'method' => 'get'
            ],
            'post' => [
                'method' => 'post'
            ],
        ];
    }

    private function setupActions()
    {
        $controller = $this->controller;
        $paramSegment = '{'.$this->name.'}';

        $this->routeNode->get("$controller@index", 'index');
        $this->routeNode->get("$controller@create", 'create')->segment($this->getCreateActionSegments());
        $this->routeNode->post("$controller@store", 'store');
        $this->routeNode->get("$controller@show", 'show')->segment($paramSegment);
        $this->routeNode->get("$controller@edit", 'edit')->segment($this->getEditActionSegments());
        $this->routeNode->put("$controller@update", 'update')->segment($paramSegment);
        $this->routeNode->delete("$controller@destroy", 'destroy')->segment($paramSegment);
    }

    private function getCreateActionSegments()
    {
        $segments = [];
        foreach ($this->routeNode->getLocales() as $locale) {
            $translationKey = 'Webflorist-RouteTree::routetree.createPathSegment';
            $translationLocale = Lang::hasForLocale($translationKey, $locale) ? $locale : 'en';
            $segments[$locale] = __($translationKey, [], $translationLocale);
        }
        return $segments;
    }

    private function getEditActionSegments()
    {
        $paramSegment = '{'.$this->name.'}';
        $segments = [];
        foreach ($this->routeNode->getLocales() as $locale) {
            $translationKey = 'Webflorist-RouteTree::routetree.editPathSegment';
            $translationLocale = Lang::hasForLocale($translationKey, $locale) ? $locale : 'en';
            $segments[$locale] = $paramSegment.'/'.__($translationKey, [], $translationLocale);
        }
        return $segments;
    }

    public function only(array $actionsOnly)
    {
        foreach ($this->routeNode->getActions() as $actionName => $routeAction) {
            if (array_search($actionName,$actionsOnly) === false) {
                $this->routeNode->removeAction($actionName);
            }
        }
    }

    public function except(array $actionsExcept)
    {
        foreach ($this->routeNode->getActions() as $actionName => $routeAction) {
            if (array_search($actionName,$actionsExcept) !== false) {
                $this->routeNode->removeAction($actionName);
            }
        }
    }

}