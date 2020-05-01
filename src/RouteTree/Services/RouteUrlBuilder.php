<?php

namespace Webflorist\RouteTree\Services;

use Webflorist\RouteTree\RouteAction;
use Webflorist\RouteTree\RouteNode;
use Webflorist\RouteTree\Exceptions\ActionNotFoundException;
use Webflorist\RouteTree\Exceptions\UrlParametersMissingException;
use Webflorist\RouteTree\RouteTree;

/**
 * Class RouteUrlBuilder
 *
 * This class is used to build URLs
 * via fluent setters and a __toString() method.
 *
 * It's returned by the route_node_url() helper,
 * and the getUrl() methods of
 * RouteNode and RouteAction.
 *
 * @package Webflorist\RouteTree
 */
class RouteUrlBuilder
{
    /**
     * The RouteNode to build an URL to.
     *
     * @var RouteNode
     */
    private $routeNode;

    /**
     * Name of the action (e.g. index, edit, create, show, etc.)
     * to build the URL to.
     *
     * (Omit for auto-detection).
     *
     * @var string
     */
    private $action;

    /**
     * An associative array of [parameterName => parameterValue]
     * pairs to be used for any route-parameters in the url
     * (default=current route-parameters).
     *
     * @var array|null
     */
    private $parameters;

    /**
     * The language this url should be generated for (default=current locale).
     *
     * @var string|null
     */
    private $locale;

    /**
     * Should the URL be absolute or relative?
     * (Configurable; defaults to true)
     *
     * @var bool|null
     */
    private $absolute;

    /**
     * RouteUrlBuilder constructor.
     *
     * @param string|RouteNode $node The node-id for which this url is generated, or a RouteNode object. (default=current node).
     * @param string $action The node-action for which this url is generated (default='index|get').
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * @param string $locale The language this url should be generated for (default=current locale).
     * @param bool $absolute Create absolute paths instead of relative paths (default=true/configurable).
     * @return string
     * @throws \Webflorist\RouteTree\Exceptions\NodeNotFoundException
     */
    public function __construct($node = null, $action = null, $parameters = null, $locale = null, $absolute = null)
    {

        $this->routeNode =
            is_string($node) ? route_tree()->getNode($node) : (
            $node instanceof RouteNode ? $node : route_tree()->getCurrentNode()
            );

        $this->action = $action ?? $this->guessAction();

        $this->parameters = $parameters;
        $this->locale = $locale;
        $this->absolute = $absolute;
    }

    /**
     * State the name of the action (e.g. index, edit, create, show, etc.)
     * to build the URL to.
     *
     * (Omit for auto-detection).
     *
     * @param string $action
     * @return RouteUrlBuilder
     */
    public function action(string $action): RouteUrlBuilder
    {
        $this->action = $action;
        return $this;
    }

    /**
     * State an associative array of [parameterName => parameterValue]
     * pairs to be used for any route-parameters in the url
     * (default=current route-parameters).
     *
     * @param array|null $parameters
     * @return RouteUrlBuilder
     */
    public function parameters(array $parameters): RouteUrlBuilder
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * State the language this url should be generated for
     * (default=current locale).
     *
     * @param string|null $locale
     * @return RouteUrlBuilder
     */
    public function locale(string $locale): RouteUrlBuilder
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Create absolute paths instead of relative paths
     * (default=true/configurable).
     *
     * @param bool|null $absolute
     * @return RouteUrlBuilder
     */
    public function absolute(bool $absolute = true): RouteUrlBuilder
    {
        $this->absolute = $absolute;
        return $this;
    }

    /**
     * Returns the RouteAction object to which the URL should be generated.
     *
     * @return RouteAction
     * @throws ActionNotFoundException
     */
    private function getRouteAction(): RouteAction
    {
        if ($this->routeNode->hasAction($this->action)) {
            return $this->routeNode->getAction($this->action);
        }

        throw new ActionNotFoundException('Node with Id "' . $this->routeNode->getId() . '" does not have the action "' . $this->action . '""');
    }

    /**
     * Tries to accumulate all path-parameters needed for an URL to $routeAction.
     * The parameters can be stated as an associative array with $parameters.
     * If not all required parameters are stated, the missing ones are tried to be auto-fetched,
     * which is only possible, if the parent-nodes they belong to are currently active.
     *
     * @param RouteAction $routeAction
     * @param string|null $locale : The language to be used for auto-fetching the parameter-values.
     * @return array
     * @throws UrlParametersMissingException
     */
    private function autoFillPathParameters(RouteAction $routeAction, ?string $locale)
    {

        // Init the return-array.
        $return = [];

        // Get all parameters needed for the path to this action.
        $requiredParameters = $routeAction->getPathParameters($locale);

        if (count($requiredParameters) > 0) {

            // We try filling $return with $this->parameters first, since the caller specifically requested those.
            $this->fillParameterArray($this->parameters, $requiredParameters, $return);

            // If not all required parameters were stated in the handed over $parameters-array,
            // we try to auto-fetch them from the parents of this node, if they are currently active.
            if (count($requiredParameters) > 0) {

                // Get active values of current root line parameters.
                $currentPathParameters = [];
                foreach ($this->routeNode->getRootLineParameters() as $routeParameter) {
                    if ($routeParameter->isActive()) {
                        $currentPathParameters[$routeParameter->getName()] = $routeParameter->getActiveRouteKey($locale);
                    }
                }

                // We try filling $return with the still $requiredParameters from $currentPathParameters.
                $this->fillParameterArray($currentPathParameters, $requiredParameters, $return);

                // If there are still undetermined parameters missing, we throw an error
                if (count($requiredParameters) > 0) {
                    throw new UrlParametersMissingException('URL could not be generated due to the following undetermined parameter(s): ' . implode(',', $requiredParameters));
                }
            }
        }

        return $return;
    }

    /**
     * Tries to fill $targetParameters
     * with the keys stated in $requiredParameters
     * taken from $sourceParameters.
     *
     * If successful, found parameters
     * are removed from $requiredParameters
     *
     * @param $sourceParameters
     * @param $requiredParameters
     * @param $targetParameters
     */
    protected function fillParameterArray($sourceParameters, &$requiredParameters, &$targetParameters)
    {
        foreach ($requiredParameters as $key => $parameter) {
            if (is_array($sourceParameters) && isset($sourceParameters[$parameter])) {
                $targetParameters[$parameter] = $sourceParameters[$parameter];
                unset($requiredParameters[$key]);
            }
        }
    }

    /**
     * Generates the URL.
     *
     * @return string
     * @throws ActionNotFoundException
     * @throws UrlParametersMissingException
     */
    public function generate(): string
    {
        $locale = $this->locale;
        // If no language is specifically stated, we use the current locale.
        RouteTree::establishLocale($locale);

        $routeAction = $this->getRouteAction();

        $absolute = $this->absolute;
        if (is_null($this->absolute)) {
            $absolute = config('routetree.absolute_urls');
        }

        return route(
            $routeAction->getRouteName($locale),
            $this->autoFillPathParameters($routeAction, $locale),
            $absolute
        );
    }

    public function __toString()
    {
        return $this->generate();
    }

    /**
     * Guesses the most appropriate action to
     * generate the link to.
     *
     * @return string|null
     */
    private function guessAction()
    {
        if ($this->routeNode->hasAction('index')) {
            return 'index';
        }
        if ($this->routeNode->hasAction('get')) {
            return 'get';
        }
        if (count($this->routeNode->getActions())>0) {
            return $this->routeNode->getActions()[0]->getName();
        }
        return null;
    }

}