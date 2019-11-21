<?php

namespace Webflorist\RouteTree\Domain;


use Illuminate\Support\Arr;
use Webflorist\RouteTree\Exceptions\UrlParametersMissingException;
use Webflorist\RouteTree\Interfaces\TranslatableRouteKey;
use Webflorist\RouteTree\RouteTree;

/**
 * Payload for RouteNodes.
 *
 * Class RoutePayload
 * @package Webflorist\RouteTree
 *
 * Default properties:
 * ===================
 * @property string $title              Page's Meta-Title.
 * @property string $navTitle           Page's Nav-Title.
 * @property string $h1Title            Page's H1-Title.
 *
 * Setters for default properties:
 * ===============================
 * @method   RoutePayload               title($title, string $forAction = null)
 * @method   RoutePayload               navTitle($navTitle, string $forAction = null)
 * @method   RoutePayload               h1Title($h1Title, string $forAction = null)
 *
 */
class RoutePayload
{

    /**
     * The RouteNode this payload belongs to.
     *
     * @var RouteNode
     */
    private $routeNode;

    /**
     * The language-file-key to be used for auto-translation of payload.
     *
     * Gets determined automatically.
     *
     * @var string
     */
    private $langFile = null;

    /**
     * Action to be used for the next get/set call.
     *
     * @var array
     */
    private $actionPayloads;

    /**
     * Payload constructor.
     *
     * Can take a payload in array-form
     * and populate this object with it.
     *
     * @param RouteNode $routeNode
     * @param array|null $payloadArray
     */
    public function __construct(RouteNode $routeNode, array $payloadArray = null)
    {
        $this->routeNode = $routeNode;

        $this->setDataLangFile();

        if (!is_null($payloadArray)) {
            foreach ($payloadArray as $itemKey => $itemValue) {
                $this->$itemKey($itemValue);
            }
        }
    }

    /**
     * Set the location of the language-file to be used for the translation of meta-data.
     */
    protected function setDataLangFile()
    {

        // Set the base-folder for localization-files as stated in the config.
        $this->langFile = config('routetree.localization.base_folder') . '/';

        // Every parent node is a subdirectory of the pages-directory.
        // So we just get the full name of the parent node (if one exists and is not root),
        // and replace the dots with slashes.
        if ($this->routeNode->hasParentNode() && $this->routeNode->getParentNode()->hasParentNode()) {
            $this->langFile .= str_replace('.', '/', $this->routeNode->getParentNode()->getId()) . '/';
        }

        // Finally append the file-name for route-tree related translations as set in the config.
        $this->langFile .= config('routetree.localization.file_name');

    }

    /**
     * Magic setter to set payload-properties,
     * via a method named like the desired property.
     *
     * @param $payloadKey
     * @param $arguments
     * @return $this
     */
    public function __call($payloadKey, $arguments)
    {
        $payloadValue = $arguments[0];
        $action = $arguments[1] ?? null;
        $this->set($payloadKey, $payloadValue, $action);
        return $this;
    }

    /**
     * Magic getter returns null for
     * all non-existent properties.
     *
     * @param string $payloadKey
     * @return mixed
     */
    public function __get($payloadKey)
    {
        return null;
    }

    /**
     * Set a payload.
     * $action parameter allows setting of action-specific payload.
     *
     * @param $payloadKey
     * @param $payloadValue
     * @param string|null $forAction
     * @return $this
     */
    public function set($payloadKey, $payloadValue, ?string $forAction = null)
    {
        if ($forAction !== null) {
            if (!isset($this->actionPayloads[$forAction])) {
                $this->actionPayloads[$forAction] = [];
            }
            $this->actionPayloads[$forAction][$payloadKey] = $payloadValue;
        } else {
            $this->$payloadKey = $payloadValue;
        }
        return $this;
    }

    public function get(string $payloadKey, ?array $parameters = null, ?string $locale = null, ?string $action = null)
    {
        return $this->resolvePayload($payloadKey, $parameters, $locale, $action);
    }

    public function has(string $payloadKey, string $forAction = null)
    {
        if ($forAction !== null && isset($this->actionPayloads[$forAction][$payloadKey])) {
            return true;
        }
        return isset($this->$payloadKey);
    }

    /**
     * "Translate" a payload.
     *
     * @param string $payloadKey Name of the payload.
     * @param string $locale The locale to translate to (default=current locale).
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters the payload should be fetched for (default=current route-parameters).
     * @param string|null $action If an action is stated, you can set data action specific (e.g. "mynode_show" with auto-translation).
     * @return mixed
     */
    protected function resolvePayload(string $payloadKey, ?array $parameters = null, ?string $locale = null, ?string $action = null)
    {
        RouteTree::establishLocale($locale);
        RouteTree::establishRouteParameters($parameters);

        $payload = $this->$payloadKey;
        if ($action !== null && isset($this->actionPayloads[$action][$payloadKey])) {
            $payload = $this->actionPayloads[$action][$payloadKey];
        }

        // If payload is a LocaleMap and contains an element for this language, that's our new $payload.
        if ($payload instanceof LanguageMapping && $payload->has($locale)) {
            $payload = $payload->get($locale);
        }

        // If $payload is a callable, we retrieve the payload for this language by calling it.
        if (is_callable($payload)) {
            $payload = call_user_func($payload, $parameters, $locale);
        }

        // If we still haven't got a payload,
        // and the payload belongs to a parameter-node,
        // which has a model attached,
        // we try getting the payload from the model.
        if (is_null($payload) && $this->routeNode->hasParameter() && $this->routeNode->parameter->hasModel()) {
            /** @var TranslatableRouteKey $modelClass */
            $modelClass = $this->routeNode->parameter->getModel();
            $modelPayload = $modelClass::getRoutePayload($payloadKey, $parameters, $locale, $action);
            if (!is_null($modelPayload)) {
                $payload = $modelPayload;
            }
        }

        // Try using auto-translation as next option.
        if (is_null($payload)) {
            $translationKey = $payloadKey . '.' . $this->routeNode->getName();

            // If an action was explicitly stated, we append "_$action" to the translation-key.
            if ($action !== null) {
                $translationKey .= '_' . $action;
            }
            $autoTranslatedValue = $this->performAutoTranslation($translationKey, $parameters, $locale);
            if ($autoTranslatedValue !== false) {
                $payload = $autoTranslatedValue;
            }
        }




        // If a payload was found and is an array,
        // it might be a nested routeKey-mapping,
        // if this route has a parameters.
        if (is_array($payload) && $this->routeNode->hasParameter()) {
            $dotNotatedParameters =  implode('.',$parameters);
            if (Arr::has($payload, $dotNotatedParameters)) {
                $payload = Arr::get($payload, $dotNotatedParameters);
            }
        }

        return $payload;
    }

    /**
     * Tries to auto-translate a stated key into a stated language within $this->langFile.
     *
     * @param string $key The translation-key to be translated.
     * @param array $parameters An associative array of [parameterName => parameterValue] that should be passed to the translation (default=current route-parameters).
     * @param string $language The language to be used for translation.
     * @return bool|string
     */
    public function performAutoTranslation($key, $parameters, $language)
    {

        // Translation-Parameters for replacement should always be an array.
        if (is_null($parameters)) {
            $parameters = [];
        }

        // Set the translation key to be used for getting the data.
        $translationKey = $this->langFile . '.' . $key;

        // If a translation for this language exists, we return that as the data.
        if (\Lang::hasForLocale($translationKey, $language)) {
            return trans($translationKey, $parameters, $language);
        }

        return false;

    }


    /**
     * Get the page title of this node (defaults to the ucfirst-ified node-name).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @param null $action
     * @return string
     * @throws UrlParametersMissingException
     */
    public function getTitle(?array $parameters = null, ?string $locale = null, $action = null): string
    {
        $this->establishAction($action);
        $title = $this->resolvePayload('title', $parameters, $locale, $action);

        if (is_string($title)) {
            return $title;
        }

        // Fallback for resources is to get the action specific default-title from the RouteResource,
        // if $action set.
        if (!is_null($action) && $this->routeNode->isResource()) {
            return $this->routeNode->resource->getActionTitle($action, $parameters, $locale);
        }

        // If the title of an action was requested, we fall back to the title of the node,
        // if no action-specific title was found.
        if (!is_null($action)) {
            return $this->getTitle($parameters, $locale, false);
        }

        // Per default we fall back to the upper-cased node-name.
        return ucfirst($this->routeNode->getName());
    }

    /**
     * Get the page title to be used in navigations (e.g. breadcrumbs or menus) of this node (defaults to the result of $this->getTitle()).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @return string
     * @throws UrlParametersMissingException
     */
    public function getNavTitle(?array $parameters = null, ?string $locale = null, $action = null): string
    {
        $this->establishAction($action);

        // Try retrieving navTitle.
        $title = $this->resolvePayload('navTitle', $parameters, $locale, $action);

        // If no title could be determined, we fall back to the result of the $this->getTitle() call.
        if (is_null($title)) {
            return $this->getTitle($parameters, $locale, $action);
        }

        return $title;
    }

    /**
     * Get the page title to be used in the page's h1 tag (defaults to the result of $this->getTitle()).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @return string
     * @throws UrlParametersMissingException
     */
    public function getH1Title(?array $parameters = null, ?string $locale = null, $action = null): string
    {
        $this->establishAction($action);

        // Try retrieving navTitle.
        $title = $this->resolvePayload('h1Title', $parameters, $locale, $action);

        // If no title could be determined, we fall back to the result of the $this->getTitle() call.
        if (is_null($title)) {
            return $this->getTitle($parameters, $locale, $action);
        }

        return $title;
    }

    /**
     * @param string|false|null $action
     * @return string|null
     */
    protected function establishAction(&$action)
    {

        // If node is active, we try to retrieve action-specific node.
        if (is_null($action) && $this->routeNode->isActive()) {
            $action = route_tree()->getCurrentAction()->getName();
        }

        if ($action === false) {
            $action = null;
        }
    }


}
