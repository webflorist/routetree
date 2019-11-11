<?php

namespace Webflorist\RouteTree\Domain;


use Webflorist\RouteTree\RouteTree;

/**
 * Payload for RouteNodes.
 *
 * Class RoutePayload
 * @package Webflorist\RouteTree
 *
 * Default properties:
 * ===================
 * @property string                     $title              Page Title.
 *
 * Setters for default properties:
 * ===============================
 * @method   RoutePayload                      $title(string $title)
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
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $this->$name = $arguments[0];
        return $this;
    }

    /**
     * Magic getter returns null for
     * all non-existent properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return null;
    }


    /**
     * Get custom data.
     *
     * Tries retrieving the data for this key in the following order:
     *  - If data for this key was set via setData() (or any magic method; e.g. setMyCustomData), that is returned.
     *  - Otherwise auto-translation is used, using the hierarchical language-file,
     *    the custom data-key as an array-key, and the current node-name as that array's key.
     *    ( e.g. if this node has the id 'about.team.it' and custom-data 'abstract' should be auto-translated,
     *     it's language file should be located at 'resources/lang/<locale>/pages/about/team'
     *     and that language file should include an array called 'abstract' containing an element with the key 'it'.
     *
     *
     * @param $key
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters the data should be fetched for (default=current route-parameters).
     * @param string $locale The language the data should be fetched for (default=current locale).
     * @param null $action If an action is stated, you can set data action specific (e.g. "title_show" in node-generation or "mynode_show" with auto-translation).
     * @return mixed
     */
    public function getData($key, $parameters = null, $locale = null, $action = null)
    {
        // If no language is specifically stated, we use the current locale
        RouteTree::establishLocale($locale);

        // If an action was explicitly stated, we use "$key_$action" as the array-key we are looking for.
        if ($action !== null) {
            $array_key = $key . '_' . $action;
        }

        // If this data was specifically set...
        if (isset($this->data[$array_key])) {

            // If data is a callable, we retrieve the data for this language by calling it.
            if (is_callable($this->data[$array_key])) {
                return call_user_func($this->data[$array_key], $parameters, $locale);
            }



        }


    }

    /**
     * "Translate" a payload.
     *
     * @param string $name Name of the payload.
     * @param string $locale The locale to translate to (default=current locale).
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters the data should be fetched for (default=current route-parameters).
     * @param null $action If an action is stated, you can set data action specific (e.g. "title_show" in node-generation or "mynode_show" with auto-translation).
     * @return mixed
     */
    public function trans(string $name, ?string $locale=null, ?array $parameters = null, ?string $action = null)
    {

        // If payload is a string, we simply return that. There is nothing to translate.
        if (is_string($this->$name)) {
            return $this->$name;
        }

        RouteTree::establishLocale($locale);

        // If payload is an array and contains an element for this language, we return that.
        if (is_array($this->$name) && isset($this->$name[$locale])) {
            return $this->$name[$locale];
        }

        // If no parameters were handed over, we use current route-parameters as default.
        RouteTree::establishRouteParameters($parameters);

        // If payload is a callable, we retrieve the data for this language by calling it.
        if (is_callable($this->$name)) {
            return call_user_func($this->$name, $parameters, $locale);
        }

        // Try using auto-translation as next option.
        $translationKey = $name . '.' . $this->routeNode->getName();

        // If an action was explicitly stated, we append "_$action" to the translation-key.
        if ($action !== null) {
            $translationKey .= '_' . $action;
        }
        $autoTranslatedValue = $this->performAutoTranslation($translationKey, $parameters, $locale);

        if ($autoTranslatedValue !== false) {
            return $autoTranslatedValue;
        }

        // Per default we return false to indicate no data was found.
        return false;
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


}
