<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 20.04.2016
 * Time: 16:39
 */

namespace Nicat\RouteTree;

class RouteNode {

    /**
     * @var RouteNode|null
     */
    protected $parentNode = null;

    protected $childNodes = [];

    protected $name = '';

    protected $id = '';

    protected $paths = [];

    /**
     * Should the path-segment of this node be inherited to it's children (default=true).
     *
     * @var bool
     */
    protected $inheritPath = true;

    protected $middleware = [];

    /**
     * @var string|null
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * @var RouteAction[]
     */
    protected $actions = [];

    protected $langFile = 'pages/pages';

    protected $values = [];

    /**
     * @var \Callable|array|null
     */
    protected $pageTitle = null;

    protected $parameter = null;

    /**
     * @var \Callable[]|array[]|null
     */
    protected $data = [];

    /**
     * RouteNode constructor.
     * @param string $name
     * @param RouteNode $parentNode
     * @param null $path
     */
    public function __construct($name='', RouteNode $parentNode = null, $path=null)
    {
        $this->name = $name;

        // Set parentNode and overtake certain data from parent.
        if (!is_null($parentNode)) {
            $this->setParentNode($parentNode);
        }

        // Append the route-name to the id.
        $this->id .= $this->name;

        $this->setPaths($path);

        return $this;

    }

    /**
     * @return null|string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param null|string $namespace
     * @return RouteNode
     */
    public function setNamespace($namespace)
    {
        $this->namespace .= '\\'.$namespace;
        return $this;
    }

    protected function setParentNode(RouteNode $parentNode) {

        // Set the parent node.
        $this->parentNode = $parentNode;

        // Set this node as a child node of the parent.
        $this->parentNode->addChildNode($this);

        // Overtake id from parentNode into current node.
        if (strlen($this->parentNode->id)>0) {
            $this->id = $this->parentNode->id . '.';
        }

        // Overtake namespace from parentNode into current node.
        if (strlen($this->parentNode->namespace)>0) {
            $this->namespace = $this->parentNode->namespace;
        }

        // Overtake middleware from parentNode into current node.
        if (count($parentNode->middleware)>0) {
            foreach ($parentNode->middleware as $middlewareKey => $middlewareData) {
                if (isset($middlewareData['inherit']) && ($middlewareData['inherit'] === true)) {
                    $this->addMiddleware($middlewareKey, $middlewareData['parameters'], $middlewareData['inherit']);
                }
            }
        }

        $this->setLangFile();
    }

    /**
     * @return RouteNode[]
     */
    public function hasParentNode() {

        return is_a($this->parentNode,RouteNode::class);
    }

    /**
     * @return RouteNode|null
     */
    public function getParentNode() {

        return $this->parentNode;
    }

    /**
     * @return RouteNode[]
     */
    public function getParentNodes() {

        $parentNodes = [];

        $this->accumulateParentNodes($parentNodes);

        $parentNodes = array_reverse($parentNodes);

        return $parentNodes;
    }

    protected function accumulateParentNodes(&$parentNodes) {
        if (is_a($this->parentNode,RouteNode::class)) {
            array_push($parentNodes, $this->parentNode);
            $this->parentNode->accumulateParentNodes($parentNodes);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        if (is_callable($this->values)) {
            return call_user_func($this->values);
        }
        return $this->values;
    }

    /**
     * @param array|\Closure $values
     * @return RouteNode $this
     */
    public function setValues($values=[])
    {
        $this->values = $values;
        return $this;
    }

    protected function setLangFile() {

        // Translations always reside within the pages-directory.
        $this->langFile = 'pages/';

        // Every parent node is a subdirectory of the pages-directory.
        // So we just get the full name of the parent node (if one exists),
        // and replace the dots with slashes.
        if ((!is_null($this->parentNode) && strlen($this->parentNode->id)>0)) {
            $this->langFile .= str_replace('.','/',$this->parentNode->id) . '/';
        }

        // The file-name for route-tree related data is 'pages.php'
        $this->langFile .= 'pages';

    }

    protected function addChildNode(RouteNode $childNode) {
        $this->childNodes[$childNode->name] = $childNode;
    }

    public function hasChildNodes() {
        if (count($this->childNodes)>0) {
            return true;
        }
        return false;
    }


    /**
     * @return RouteNode[]
     */
    public function getChildNodes() {
        return $this->childNodes;
    }


    /**
     * @return RouteNode
     */
    public function getChildNode($nodeName='') {
        if ($this->hasChildNode($nodeName)) {
            return $this->childNodes[$nodeName];
        }
        else {
            return false;
        }
    }

    public function hasChildNode($nodeName='') {
        if (isset($this->childNodes[$nodeName])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $language
     * @return string
     */
    public function getPath($language = null)
    {
        // If no language is specifically stated, we use the current locale
        if (is_null($language)) {
            $language = \App::getLocale();
        }

        return $this->paths[$language];
    }

    /**
     * @param null $language
     * @return string
     */
    public function getUrl($parameters=[], $language = null)
    {

        if ($this->hasAction('index')) {
            return $this->getUrlByAction('index', $parameters, $language);
        }

        if ($this->hasAction('get')) {
            return $this->getUrlByAction('get', $parameters, $language);
        }

        if (count($this->actions)>0) {
            return $this->getUrlByAction(key($this->actions), $parameters, $language);
        }

        // TODO: throw exception, that no url is set
    }

    /**
     * @param null $language
     * @return string
     */
    public function getUrlByAction($action='index', $parameters=[], $language = null)
    {
        if ($this->hasAction($action)) {
            return $this->getAction($action)->getUrl($parameters, $language);
        }

        // TODO: throw exception, that no url is set
    }

    /**
     * @return string
     */
    public function isActive($parameters=null)
    {
        if (app()[RouteTree::class]->getCurrentNode() === $this) {

            if (is_null($parameters)) {
                return true;
            }

            $currentParameters = \Route::current()->parameters();
            $allParametersSet = true;
            foreach ($parameters as $desiredParameterName => $desiredParameterValue) {
                if (!isset($currentParameters[$desiredParameterName]) || ($currentParameters[$desiredParameterName] !== $desiredParameterValue)) {
                    $allParametersSet = false;
                }
            }
            return $allParametersSet;
        }

        return false;
    }

    /**
     * @param array|\Callable|string $pageTitle
     * @return RouteNode
     */
    public function setPageTitle($pageTitle=[])
    {
        // If $pageTitle is a string, we set it's value for each language.
        if (is_string($pageTitle)) {
            foreach (\Config::get('app.locales') as $language => $fullLanguage) {
                $this->pageTitle[$language] = $pageTitle;
            }
        }
        // In all other cases ($pageTitle is callable or array), we overtake $pageTitle as is.
        else {
            $this->pageTitle = $pageTitle;
        }

        return $this;
    }

    /**
     * @param null $language
     * @return string
     */
    public function getPageTitle($parameters=null, $language=null)
    {

        // If no language is specifically stated, we use the current locale
        if (is_null($language)) {
            $language = \App::getLocale();
        }

        // If $this->pageTitle is a callable, we retrieve the page-title for this language by calling it.
        if (is_callable($this->pageTitle)) {

            if (is_null($parameters)) {
                $parameters = \Route::current()->parameters();
            }

            return call_user_func($this->pageTitle, $parameters, $language);
        }

        // If $this->pageTitle is an array and contains an element for this language, we return that.
        if (is_array($this->pageTitle) && isset($this->pageTitle[$language])) {
            return $this->pageTitle[$language];
        }

        // Try using auto-translation as next option.

        // Set the translation key to be used for getting localized page titles.
        $translationKey = $this->langFile.'.title.'.$this->name;

        // If a translation for this language exists, we return that as the page title.
        if (\Lang::hasForLocale($translationKey, $language)) {
            return trans($translationKey, [], 'messages', $language);
        }

        // Per default we just return the upper-cased node-name.
        return ucfirst($this->name);
    }

    /**
     * @return RouteNode
     */
    public function setData($key,$data)
    {
        // If $data is a string, we set it's value for each language.
        if (is_string($data)) {
            foreach (\Config::get('app.locales') as $language => $fullLanguage) {
                $this->data[$key][$language] = $data;
            }
        }
        // In all other cases ($data is callable or array), we overtake $data as is.
        else {
            $this->data[$key] = $data;
        }

        return $this;
    }

    /**
     * @param null $language
     * @return string
     */
    public function getData($key, $parameters=null, $language=null)
    {

        // If no language is specifically stated, we use the current locale
        if (is_null($language)) {
            $language = \App::getLocale();
        }

        // If this data was specifically set...
        if (isset($this->data[$key])) {

            // If data is a callable, we retrieve the data for this language by calling it.
            if (is_callable($this->data[$key])) {

                // If no parameters were handed over, we use current route-parameters as default.
                if (is_null($parameters)) {
                    $parameters = \Route::current()->parameters();
                }

                return call_user_func($this->data[$key], $parameters, $language);
            }

            // If data is an array and contains an element for this language, we return that.
            if (is_array($this->data[$key]) && isset($this->data[$key][$language])) {
                return $this->data[$key][$language];
            }

        }

        // Try using auto-translation as next option.

        // Set the translation key to be used for getting the data.
        $translationKey = $this->langFile.'.'.$key.'.'.$this->name;

        // If a translation for this language exists, we return that as the data.
        if (\Lang::hasForLocale($translationKey, $language)) {
            return trans($translationKey, [], 'messages', $language);
        }

        // Per default we return false to indicate no data was found.
        return false;
    }

    /**
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function addMiddlewareFromArray($middlewareArray = []) {
        foreach ($middlewareArray as $middlewareKey => $middlewareData) {
            $this->addMiddleware($middlewareKey, $middlewareData['parameters'], $middlewareData['inherit']);
        }
    }

    public function addMiddleware($name='', $parameters=[], $inherit=true) {
        $this->middleware[$name] = [
            'parameters' => $parameters,
            'inherit' => $inherit
        ];
    }

    public function addAction(RouteAction $routeAction) {

        // Set the RouteNode within the RouteAction.
        $routeAction->setRouteNode($this);

        // Add the action to $this->actions
        $this->actions[$routeAction->getAction()] = $routeAction;

        return $this;
    }

    public function getAction($action) {
        if ($this->hasAction($action))  {
            return $this->actions[$action];
        }
        else {
            return false;
        }
    }

    public function hasAction($action) {
        if (isset($this->actions[$action])) {
            return true;
        }
        return false;
    }

    protected function getInheritedPaths() {

        if ($this->hasParentNode()) {
            if ($this->parentNode->inheritPath) {
                return $this->parentNode->paths;
            }
            else {
                return $this->parentNode->getInheritedPaths();
            }
        }

        return false;
    }

    public function setPaths($path=null) {

        // Set the translation key to be used for getting localized paths.
        $pathTranslationKey = $this->langFile.'.path.'.$this->name;

        // Get the inherited paths.
        $inheritedPaths = $this->getInheritedPaths();

        // Iterate through configured languages.
        foreach (\Config::get('app.locales') as $language => $fullLanguage) {

            // If an inherited path could be determined, we use that.
            if ($inheritedPaths !== false) {

                $this->paths[$language] = $inheritedPaths[$language];
            }
            // If no inherited path could be determined, we start the path with the language.
            else {
                $this->paths[$language] = $language;
            }

            // Standard path segment is the name of this route node.
            $pathSegment = $this->name;

            // If $path is an array and contains an entry for this language, we use that.
            if (is_array($path) && isset($path[$language])) {
                $pathSegment = $path[$language];
            }
            // If $path is a string, we use that.
            else if (is_string($path)){
                $pathSegment = $path;
            }
            // If a translation for this language exists, we use that as path segment.
            else if (\Lang::hasForLocale($pathTranslationKey, $language)) {
                $pathSegment = trans($pathTranslationKey, [], 'messages', $language);
            }

            // Append the path segment for the current node.
            if (strlen($pathSegment) > 0) {
                $this->paths[$language] .= '/' . $pathSegment;
            }

            // If the path segment is a parameter, we also store it in $this->parameter.
            if ((substr($pathSegment,0,1) === '{') && (substr($pathSegment,-1) === '}')) {
                $this->parameter = str_replace('{','',str_replace('}','',$pathSegment));
            }
        }
    }

    public function generateRoutesOfNodeAndChildNodes() {
        $this->generateRoutes();

        if ($this->hasChildNodes()) {
            foreach ($this->getChildNodes() as $childNode) {
                $childNode->generateRoutesOfNodeAndChildNodes();
            }
        }

    }

    /**
     * Generate the routes of all actions for this node.
     */
    public function generateRoutes() {
        if (count($this->actions)>0) {
            foreach ($this->actions as $key => $routeAction) {
                $routeAction->generateRoutes();
            }
        }
    }

    /**
     * Sets, if the path-segment of this node should be inherited to it's children (default=true).
     *
     * @param boolean $inheritPath
     * @return RouteNode
     */
    public function setInheritPath($inheritPath)
    {
        $this->inheritPath = $inheritPath;
        return $this;
    }


}