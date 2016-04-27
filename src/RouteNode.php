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

    protected $langFolder = 'pages/';

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

        $this->setLangFolder();
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

    protected function setLangFolder() {

        // Translations always reside within the pages-directory.
        $this->langFolder = 'pages/';

        // Every parent node is a subdirectory of the pages-directory.
        // So we just get the full name of the parent node (if one exists),
        // and replace the dots with slashes.
        if ((!is_null($this->parentNode) && strlen($this->parentNode->id)>0)) {
            $this->langFolder .= str_replace('.','/',$this->parentNode->id) . '/';
        }

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

    public function getChildNodes() {
        return $this->childNodes;
    }

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
     * @return string
     */
    public function isActive()
    {
        if (app()[RouteTree::class]->getCurrentNode() === $this) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getPageTitle($language=null)
    {

        // If no language is specifically stated, we use the current locale
        if (is_null($language)) {
            $language = \App::getLocale();
        }

        // Set the translation key to be used for getting localized page titles.

        // The translation file-name for paths is 'titles',
        // and the key is the name of the current node.
        $translationKey = $this->langFolder.'titles.'.$this->name;

        // If a translation for this language exists, we return that as the page title.
        if (\Lang::hasForLocale($translationKey, $language)) {
            return trans($translationKey, [], 'messages', $language);
        }

        // Per default we just return the upper-cased node-name.
        return ucfirst($this->name);
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
        array_push($this->actions, $routeAction);

        return $this;
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

        // The translation file-name for paths is 'paths',
        // and the key is the name of the current node.
        $pathTranslationKey = $this->langFolder.'paths.'.$this->name;

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

    public function generateRoutes() {

        // Generate the routes of all actions for this node.
        if (count($this->actions)>0) {
            foreach ($this->actions as $key => $routeAction) {
                $routeAction->generateRoutes();
            }
        }
    }

    /**
     * @param boolean $inheritPath
     * @return RouteNode
     */
    public function setInheritPath($inheritPath)
    {
        $this->inheritPath = $inheritPath;
        return $this;
    }

}