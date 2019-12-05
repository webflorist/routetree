<?php

namespace Webflorist\RouteTree\Domain;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Exceptions\NoValidModelException;
use Webflorist\RouteTree\Interfaces\ProvidesRouteKeyList;
use Webflorist\RouteTree\Interfaces\ProvidesRoutePayload;
use Webflorist\RouteTree\Interfaces\TranslatesRouteKey;
use Webflorist\RouteTree\RouteTree;

/**
 * Class RouteParameter
 *
 * This class manages data for RouteNodes,
 * that contain a route-parameter.
 *
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
     * A complete list of route-keys for this RouteParameter.
     * (Either one-dimensional for all languages
     * or multi-dimensional per language).
     *
     * Can be used to resolve all specific URLs for routes
     * including a parameter. (e.g. for Sitemap-generation.)
     *
     * @var array
     */
    private $routeKeyList;

    /**
     * Regular expression requirements of route parameters.
     *
     * @var array
     */
    public $regex = [];

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

    /**
     * State a model, that corresponds to this RouteParameter
     * to use for various functionality.
     *
     * @param string $model
     * @throws NoValidModelException
     */
    public function model(string $model)
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new NoValidModelException("Class '$model' does not seem to be an Eloquent Model.");
        }
        $this->model = $model;
    }

    /**
     * Add a complete list of route-keys for this RouteParameter.
     * (Either one-dimensional for all languages
     * or multi-dimensional per language).
     *
     * Can be used to resolve all specific URLs for routes
     * including a parameter. (e.g. for Sitemap-generation.)
     *
     * @param array $routeKeys
     */
    public function routeKeys(array $routeKeys)
    {
        $this->routeKeyList = $routeKeys;
    }

    /**
     * Returns an array of all route-keys this parameter
     * can have as values.
     *
     * This only works, if the route-keys were stated via routeKeys(),
     * or if a model was stated via model(),
     * that implements the interface ProvidesRouteKeyList.
     *
     * This is used by the sitemap generator and API to
     * acquire a list of all URLs.
     *
     * @param string|null $locale
     * @param array|null $parameters
     * @return array|mixed
     */
    public function getRouteKeyList(?string $locale = null, ?array $parameters = null)
    {
        RouteTree::establishLocale($locale);
        RouteTree::establishRouteParameters($parameters);

        if (!is_null($this->routeKeyList)) {
            // if $this->routeKeys is multidimensional, we assume it's localised
            if (is_array(array_values($this->routeKeyList)[0])) {
                return $this->routeKeyList[$locale];
            }
            return $this->routeKeyList;
        }

        if ($this->hasRouteKeyListProvidingModel()) {
            return $this->model::getRouteKeyList($locale, $parameters);
        }

        return [];

    }

    /**
     * Can this RouteParameter resolve it's possible route keys?
     *
     * @param string $locale
     * @param array|null $parameters
     * @return bool
     */
    public function canResolveRouteKeyList(string $locale, array $parameters = null)
    {
        return count($this->getRouteKeyList($locale, $parameters)) > 0;
    }

    /**
     * Get the name of this RouteParameter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Is this parameter currently active
     * (= present in the current path)?
     *
     * @return bool
     */
    public function isActive()
    {
        if (is_null(\Route::current())) {
            return false;
        }
        return \Route::current()->hasParameter($this->name);
    }

    /**
     * Returns the current route-key/value of this RouteParameter,
     * if present.
     *
     * @param string|null $locale
     * @return mixed|null
     */
    public function getActiveRouteKey(?string $locale = null)
    {
        if ($this->isActive()) {
            $currentParameterValue = \Route::current()->parameter($this->name);
            $translateValue = !is_null($locale) && ($locale !== app()->getLocale());

            // In case parameter is a bound model.
            if ($currentParameterValue instanceof Model) {
                $currentParameterValue = $currentParameterValue->getRouteKey();
            }

            if ($translateValue) {
                return $this->translateRouteKey($currentParameterValue, $locale, app()->getLocale());
            }
            return $currentParameterValue;
        }
        return null;
    }

    /**
     * Translates a route key from one language to another.
     *
     * This is used to achieve language-switching for parameter-routes.
     * It only works, if a routeKeyList() was stated, or if a model()
     * was stated, that implements TranslatesRouteKey.
     *
     * @param $routeKey
     * @param string $toLocale
     * @param string $fromLocale
     * @return mixed
     */
    private function translateRouteKey($routeKey, string $toLocale, string $fromLocale)
    {
        if (!is_null($this->routeKeyList) && is_array(array_values($this->routeKeyList)[0])) {
            $valueKey = array_search($routeKey, $this->routeKeyList[$fromLocale]);
            if (isset($this->routeKeyList[$toLocale][$valueKey])) {
                return $this->routeKeyList[$toLocale][$valueKey];
            }
        }

        if ($this->hasRoutekeyTranslatingModel()) {
            return $this->model::translateRouteKey($routeKey, $toLocale, $fromLocale);
        }

        return $routeKey;
    }

    /**
     * Is this RouteParameter associated
     * with an Eloquent Model?
     *
     * @return bool
     */
    public function hasModel()
    {
        return $this->model !== null;
    }

    /**
     * Is this RouteParameter associated
     * with an Eloquent Model, that
     * implements ProvidesRouteKeyList?
     *
     * @return bool
     */
    public function hasRouteKeyListProvidingModel()
    {
        return $this->hasModel() && in_array(ProvidesRouteKeyList::class, class_implements($this->model));
    }

    /**
     * Is this RouteParameter associated
     * with an Eloquent Model, that
     * implements TranslatesRouteKey?
     *
     * @return bool
     */
    public function hasRouteKeyTranslatingModel()
    {
        return $this->hasModel() && in_array(TranslatesRouteKey::class, class_implements($this->model));
    }

    /**
     * Is this RouteParameter associated
     * with an Eloquent Model, that
     * implements ProvidesRoutePayload?
     *
     * @return bool
     */
    public function hasPayloadProvidingModel()
    {
        return $this->hasModel() && in_array(ProvidesRoutePayload::class, class_implements($this->model));
    }

    /**
     * Returns the Eloquent Model associated
     * with this RouteParameter.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Check if all current route parameters have the values
     * stated in $routeKeys.
     *
     * @param array $routeKeys: As [routeParameter => routeKey] pairs (E.g. ['blog_category' => 'my-category', 'blog_article' => 'my-article']).
     * @return bool
     */
    public static function currentRouteHasRouteKeys(array $routeKeys): bool
    {
        $currentParameters = \Route::current()->parameters();
        $allParametersSet = true;
        foreach ($routeKeys as $desiredParameterName => $desiredRouteKey) {
            if (!isset($currentParameters[$desiredParameterName]) || ($currentParameters[$desiredParameterName] !== $desiredRouteKey)) {
                $allParametersSet = false;
            }
        }
        return $allParametersSet;
    }

    /**
     * Set a regular expression requirement on this RouteParameter.
     *
     * @param string|null $expression
     * @return $this
     */
    public function regex(string $expression)
    {
        $this->regex = $expression;
        return $this;
    }

    /**
     * Is a regular expression requirement set for this RouteParameter.
     *
     * @return bool
     */
    public function hasRegex()
    {
        return is_string($this->regex);
    }

    /**
     * Get the expression requirement set for this RouteParameter.
     *
     * @return bool
     */
    public function getRegex()
    {
        return $this->regex;
    }

}
