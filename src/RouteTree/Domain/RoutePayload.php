<?php

namespace Webflorist\RouteTree\Domain;

use Webflorist\RouteTree\Interfaces\ProvidesRoutePayload;
use Webflorist\RouteTree\RouteTree;

/**
 * Class RoutePayload
 *
 * This class provides functionality to
 * store/retrieve/translate custom data
 * for RouteNodes and RouteActions.
 *
 * @package Webflorist\RouteTree
 *
 * Default properties:
 * ===================
 * @property string $title              Page's Meta-Title.
 * @property string $navTitle           Page's Nav-Title.
 *
 * Setters for default properties:
 * ===============================
 * @method   RoutePayload               title($title)
 * @method   RoutePayload               navTitle($navTitle)
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
     * The RouteAction this payload belongs to
     * (in case it is an action-specific payload).
     *
     * @var RouteAction|null
     */
    private $routeAction;

    /**
     * The language-file-key to be used for auto-translation of payload.
     *
     * Gets determined automatically.
     *
     * @var string
     */
    private $langFile = null;

    /**
     * Payload constructor.
     *
     * @param RouteNode $routeNode
     * @param RouteAction|null $routeAction
     */
    public function __construct(RouteNode $routeNode, ?RouteAction $routeAction = null)
    {
        $this->routeNode = $routeNode;
        $this->routeAction = $routeAction;

        $this->setLangFile();
    }

    /**
     * Set the location of the language-file to be used for the translation of meta-data.
     */
    protected function setLangFile()
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
        $this->set($payloadKey, $payloadValue);
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
     *
     * @param $payloadKey
     * @param $payloadValue
     * @return $this
     */
    public function set($payloadKey, $payloadValue)
    {
        $this->$payloadKey = $payloadValue;
        return $this;
    }

    /**
     * Does this object have a payload
     * with key $payloadKey?
     *
     * @param string $payloadKey
     * @return bool
     */
    public function has(string $payloadKey)
    {
        if (isset($this->$payloadKey)) {
            return true;
        }
        if (!is_null($this->routeAction) && isset($this->routeNode->payload->$payloadKey)) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve a payload in the following fallback order:
     *
     * 1. Payload set directly in this class.
     * (2. Payload set in the RouteNode's RoutePayload - only if this RoutePayload is RouteAction-specific.)
     * (3. Payload returned from an Eloquent Model - only if RouteNode has a RouteParameter associated with an Eloquent Model, that implements ProvidesRoutePayload)
     * 4. Using Auto-Translation by searching for payload at a translation-key relative to the RouteNode's ID.
     *
     * @param string $payloadKey Name of the payload.
     * @param string $locale The locale to translate to (default=current locale).
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters the payload should be fetched for (default=current route-parameters).
     * @return mixed
     */
    public function get(string $payloadKey, ?array $parameters = null, ?string $locale = null)
    {
        RouteTree::establishLocale($locale);
        RouteTree::establishRouteParameters($parameters);
        $payload = $this->$payloadKey;

        // If no payload was found and this
        // is a RouteAction specific payload,
        // try falling back to the RouteNode's payload.
        if (is_null($payload) && !is_null($this->routeAction) && $this->routeNode->payload->has($payloadKey)) {
            $payload = $this->routeNode->payload->$payloadKey;
        }

        // If payload is a LanguageMapping and contains an element for this language, that's our new $payload.
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
        if (is_null($payload) && $this->routeNode->hasParameter() && $this->routeNode->parameter->hasPayloadProvidingModel()) {
            /** @var ProvidesRoutePayload $modelClass */
            $modelClass = $this->routeNode->parameter->getModel();
            $modelPayload = $modelClass::getRoutePayload($payloadKey, $parameters, $locale, (!is_null($this->routeAction) ? $this->routeAction->getName() : null));
            if (!is_null($modelPayload)) {
                $payload = $modelPayload;
            }
        }

        // Try using auto-translation as next option.
        if (is_null($payload)) {
            $translationKey = $payloadKey . '.' . $this->routeNode->getName();

            // If this is an action-specific payload, we append "_$actionName" to the translation-key.
            if ($this->routeAction !== null) {
                $translationKey .= '_' . $this->routeAction->getName();
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
            $payload = $this->resolveNestedRouteKeyPayload($parameters, $payload);
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
     * Returns the value for a specific route-key-set ($routeKeys)
     * from a multidimensional array ($payload).
     *
     * E.g.
     * $routeKeys = [
     *      'category' => 'my-blog-category',
     *      'article' => 'my-second-blog-article'
     * ];
     * $payload = [
     *      'my-blog-category' => [
     *          'my-first-blog-article' => 'This is my first blog article!',
     *          'my-second-blog-article' => 'This is my second blog article!',
     *      ]
     *      'my-other-blog-category' => [...]
     * ];
     * returns 'This is my second blog article!'
     *
     * @param array|null $routeKeys
     * @param $payload
     * @return mixed
     */
    protected function resolveNestedRouteKeyPayload(array $routeKeys, array $payload)
    {
        foreach ($routeKeys as $parameter) {
            if (isset($payload[$parameter])) {
                if (is_array($payload[$parameter])) {
                    $parametersWithoutTheCurrentOne = $routeKeys;
                    array_shift($parametersWithoutTheCurrentOne);
                    return $this->resolveNestedRouteKeyPayload($parametersWithoutTheCurrentOne, $payload[$parameter]);
                }
                return $payload[$parameter];
            }
        }
        return $payload;
    }

}
