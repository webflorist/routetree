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
     * @var RouteNode
     */
    protected $parentNode = null;

    protected $childNodes = [];

    protected $name = '';

    protected $id = '';

    protected $paths = [];

    protected $middleware = [];

    /**
     * @var string|null
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * @var RouteAction[]
     */
    protected $actions = [];

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
     * @return string
     */
    public function getPathForLanguage($language='')
    {
        return $this->paths[$language];
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->paths[\App::getLocale()];
    }

    /**
     * @return string
     */
    public function isActive()
    {
        if (app()[RouteTree::class]->getIdOfCurrentNode() === $this->id) {
            return true;
        }
        else {
            return false;
        }
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

    public function setPaths($path=null) {

        // Set the translation key to be used for getting localized paths.

        // Translations always reside within the pages-directory.
        $pathTranslationKey = 'pages/';

        // Every parent node is a subdirectory of the pages-directory.
        // So we just get the full name of the parent node (if one exists),
        // and replace the dots with slashes.
        if ((!is_null($this->parentNode) && strlen($this->parentNode->id)>0)) {
            $pathTranslationKey .= str_replace('.','/',$this->parentNode->id) . '/';
        }

        // The translation file-name for paths is 'paths',
        //  and the key is the name of the current node.
        $pathTranslationKey .= 'paths.'.$this->name;

        // Iterate through configured languages.
        foreach (\Config::get('app.locales') as $language => $fullLanguage) {

            // The path begins with the path from the parentNode.
            if (isset($this->parentNode->paths[$language])) {
                $this->paths[$language] = $this->parentNode->paths[$language];
            }
            // If no parent-node-path is set yet, we start the path with the language.
            else {
                $this->paths[$language] = $language;
            }

            // If $path is false, we do not want this node to be visible in the path at all.
            if ($path !== false) {

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

}