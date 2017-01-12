<?php
/**
 * Created by PhpStorm.
 * User: geraldb
 * Date: 20.04.2016
 * Time: 16:39
 */

namespace Nicat\RouteTree;

use Nicat\RouteTree\Exceptions\ActionNotFoundException;

class RouteNode {

    /**
     * Parent node of this node.
     *
     * @var RouteNode|null
     */
    protected $parentNode = null;

    /**
     * Child nodes of this node.
     *
     * @var array
     */
    protected $childNodes = [];

    /**
     * Name of this node.
     * (e.g. 'team').
     *
     * @var string
     */
    protected $name = '';

    /**
     * Id of this node.
     * This is the name of all parents and this node, separated by dots.
     * (e.g. 'about.company.team')
     *
     * The Id gets set automatically.
     *
     * @var string
     */
    protected $id = '';

    /**
     * An associative array with the languages as keys and the path-segments to be used for this node as values.
     *
     * @var array
     */
    protected $segments = [];

    /**
     * An associative array with the languages as keys and the full path to this node as values.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Should the path-segment of this node be inherited to it's children (default=true)?
     * This way a node can have it's own path (e.g. about/company),
     * but it's children will not have the 'company' in their paths (e.g. about/team instead of about/company/team).
     *
     * @var bool
     */
    protected $inheritPath = true;

    /**
     * Array of middlewares, actions of this node should be registered with.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The namespace, controllers should be registered with.
     *
     * @var string|null
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Array of RouteAction objects, this route-node should have.
     *
     * @var RouteAction[]
     */
    protected $actions = [];

    /**
     * The language-file-key to be used for various auto-translations.
     *
     * @var string
     */
    protected $langFile = null;

    /**
     * If this route-node is a route-parameter, it's name is stored here.
     *
     * @var null
     */
    protected $parameter = null;

    public $isResourceChild = false;

    /**
     * Array of custom-data.
     *
     * @var \Callable[]|array[]|null
     */
    protected $data = [];

    /**
     * RouteNode constructor.
     * @param string $name
     * @param RouteNode $parentNode
     * @param null $segment
     */
    public function __construct($name='', RouteNode $parentNode = null, $segment=null)
    {
        // Set the name of this node.
        $this->name = $name;

        // Set parentNode and overtake certain data from parent.
        if (!is_null($parentNode)) {
            $this->setParentNode($parentNode);
        }
        // If no parent is stated, we set the default langFile.
        else
        {
            $this->setLangFile();
        }

        // Append the route-name to the id.
        $this->id .= $this->name;

        // Set the path-segments.
        $this->setSegments($segment);

        return $this;

    }

    /**
     * Overload method to catch get- or set-calls for custom data.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (substr($name,0,3) === 'get') {
            array_unshift($arguments, lcfirst(substr($name,3)));
            return call_user_func_array([$this,'getData'],$arguments);
        }

        if (substr($name,0,3) === 'set') {
            array_unshift($arguments, lcfirst(substr($name,3)));
            return call_user_func_array([$this,'setData'],$arguments);
        }

        // TODO: return unknown method error.
    }

    /**
     * Overloading function to catch class variable get calls
     * like $node->title
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
            return $this->getData($name);
    }

    /**
     * Overloading function to catch class variable setting
     * like $node->title =  ' foo'
     *
     * @param $name
     * @param $argument
     * @return RouteNode
     */
    public function __set($name, $argument)
    {
        return $this->setData($name, $argument);
    }

    /**
     * Get the namespace to be used for controllers.
     *
     * @return null|string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Appends to the default or inherited namespace to be used for controllers.
     *
     * @param null|string $namespace
     * @return RouteNode
     */
    public function appendNamespace($namespace)
    {
        $this->namespace .= '\\'.$namespace;
        return $this;
    }

    /**
     * Set the namespace to be used for controllers.
     * This is inherited from parents and appended to inherited namespaces.
     *
     * @param null|string $namespace
     * @return RouteNode
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Sets the parent node of this node and overtakes certain data.
     *
     * @param RouteNode $parentNode
     */
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

        // Sets the language-file location.
        $this->setLangFile();
    }

    /**
     * Does this node have a parent node?
     *
     * @return RouteNode[]
     */
    public function hasParentNode() {

        return is_a($this->parentNode,RouteNode::class);
    }

    /**
     * Gets the parent node of this node.
     *
     * @return RouteNode|null
     */
    public function getParentNode() {

        return $this->parentNode;
    }

    /**
     * Gets an array of all hierarchical parent-nodes of this node
     * (with the root-node as the first element).
     *
     * @return RouteNode[]
     */
    public function getParentNodes() {

        $parentNodes = [];

        $this->accumulateParentNodes($parentNodes);

        $parentNodes = array_reverse($parentNodes);

        return $parentNodes;
    }

    /**
     * Accumulate all parent-nodes up the hierarchy.
     *
     * @param $parentNodes
     */
    protected function accumulateParentNodes(&$parentNodes) {
        if (is_a($this->parentNode,RouteNode::class)) {
            array_push($parentNodes, $this->parentNode);
            $this->parentNode->accumulateParentNodes($parentNodes);
        }
    }

    /**
     * Does this node have a parameter?
     *
     * @return bool
     */
    public function hasParameter() {

        return !is_null($this->parameter);
    }

    /**
     * Get the parameter of this node.
     *
     * @return null|string
     */
    public function getParameter() {

        return $this->parameter;
    }

    /**
     * Returns an array of all parameters and their values (if currently active) used by this node or one of it's parents.
     *
     * @param bool $activeOnly Only parameters, that are acutally present in the current route will be returned.
     * @param null $language
     * @param bool $translateValues Values is are translated into the requested language.
     * @return array
     */
    public function getParametersOfNodeAndParents($activeOnly=false, $language=null, $translateValues = false) {

        // Initialize the return-array.
        $parameters = [];

        // Get all parent nodes and add the current node.
        $rootLineNodes = $this->getParentNodes();
        array_push($rootLineNodes, $this);

        // For each node of the rootline, we check, if it is a parameter-node
        // and try getting it's value in the requested language.
        foreach ($rootLineNodes as $node) {
            if ($node->hasParameter()) {

                // Per default we set null as the parameter-value
                $value = null;

                // If the parameter is currently active, we try getting the value of it.
                if ($node->hasActiveParameter()) {

                    $value = $node->getActiveValue();

                    // If the value should be translated, we try to do that.
                    if ($translateValues) {

                        $value = $node->getValueSlug($value, $language);
                    }

                }

                // If $activeOnly is set to true, we only add this parameter to the output-array, if it has a value.
                if (!($activeOnly && is_null($value))) {
                    $parameters[$node->getParameter()] = $value;
                }
            }
        }

        return $parameters;

    }

    /**
     * Tries to get the currently active action of this node.
     * Returns false, if no action of the current node is currently active.
     *
     * @return RouteAction|bool
     */
    public function getActiveAction() {

        if (route_tree()->getCurrentAction()->getRouteNode() === $this) {
            return route_tree()->getCurrentAction();
        }

        return false;
    }

    /**
     * Tries to get the action of this node, that is currently lowest
     * in the hierarchy of root-line-actions (mostly relevant for resource actions).
     *
     * e.g. the edit action with path "/user/{user}/edit" is lower in the root-line
     * as the show action with "/user/{user}", which is again lower
     * as the index action with "/user".
     *
     * Returns false, if no action of the current node is currently in the rootline.
     *
     * @return RouteAction|bool
     */
    public function getLowestRootLineAction() {

        $currentActionUrl = route_tree()->getCurrentAction()->getUrl();

        $mostActiveRootLineAction = false;
        $mostActiveRootLineActionUrlLength = 0;
        foreach ($this->actions as $action) {
            $actionUrl = $action->getUrl();
            $actionUrlLength = strlen($actionUrl);

            if ((strpos($currentActionUrl, $actionUrl) === 0) && (strlen($actionUrl) > $mostActiveRootLineActionUrlLength)) {
                $mostActiveRootLineAction = $action;
                $mostActiveRootLineActionUrlLength = $actionUrlLength;
            }
        }

        return $mostActiveRootLineAction;

    }

    /**
     * Get the name of this node.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $parameter
     * @return RouteNode
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }

    /**
     * Set the language file of this node,
     * representing the hierarchical structure of it's parents as a folder-structure.
     */
    protected function setLangFile() {

        // Set the base-folder for localization-files as stated in the config.
        $this->langFile = config('routetree.localization.baseFolder').'/';

        // Every parent node is a subdirectory of the pages-directory.
        // So we just get the full name of the parent node (if one exists),
        // and replace the dots with slashes.
        if ((!is_null($this->parentNode) && strlen($this->parentNode->id)>0)) {
            $this->langFile .= str_replace('.','/',$this->parentNode->id) . '/';
        }

        // Finally append the file-name for route-tree related translations as set in the config.
        $this->langFile .= config('routetree.localization.fileName');

    }

    /**
     * Add a child-node.
     *
     * @param RouteNode $childNode
     */
    protected function addChildNode(RouteNode $childNode) {
        $this->childNodes[$childNode->name] = $childNode;
    }

    /**
     * Does this node have children?
     *
     * @return bool
     */
    public function hasChildNodes() {
        if (count($this->childNodes)>0) {
            return true;
        }
        return false;
    }


    /**
     * Get array of all child-nodes.
     *
     * @return RouteNode[]
     */
    public function getChildNodes() {
        return $this->childNodes;
    }


    /**
     * Gets a specific child-node.
     *
     * @param string $nodeName
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

    /**
     * Does this node have a specific child?
     *
     * @param string $nodeName
     * @return bool
     */
    public function hasChildNode($nodeName='') {
        if (isset($this->childNodes[$nodeName])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Get the Id of this node.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the path for this node (for a specific language).
     * If no language is stated, the current locale is used.
     *
     * @param string $locale
     * @return string
     */
    public function getPath($locale = null)
    {
        // If no language is specifically stated, we use the current locale
        RouteTree::establishLocale($locale);

        return $this->paths[$locale];
    }

    /**
     * Get url to the most suitable action of this node,
     * using the following order: 'index' -> 'get' -> the first element of the action-array.
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * @param string $language The language this url should be generated for (default=current locale).
     * @return string
     * @throws ActionNotFoundException
     */
    public function getUrl($parameters=null, $language = null)
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

        throw new ActionNotFoundException('Node with Id "'.$this->getId().'" does not have any action to generate an URL to.');
    }

    /**
     * Gets the url of a certain action of this node.
     *
     * @param string $action The action name (e.g. index|show|get|post|update,etc.)
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * @param string $language The language this url should be generated for (default=current locale).
     * @return string
     * @throws ActionNotFoundException
     * @throws Exceptions\UrlParametersMissingException
     */
    public function getUrlByAction($action='index', $parameters=null, $language = null)
    {
        if ($this->hasAction($action)) {
            return $this->getAction($action)->getUrl($parameters, $language);
        }

        throw new ActionNotFoundException('Node with Id "'.$this->getId().'" does not have the action "'.$action.'""');
    }

    /**
     * Checks, if the current node is active (optionally with the desired parameters).
     * 
     * @param null $parameters
     * @return string
     */
    public function isActive($parameters=null)
    {
        
        // Check, if the current node is identical to this node.
        if (route_tree()->getCurrentNode() === $this) {

            // If no parameters are specifically requested, we immediately return true.
            if (is_null($parameters)) {
                return true;
            }

            // If a set of parameters should also be checked, we get the current route-parameters,
            // check if each one is indeed set, and return the boolean result.
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
     * Checks, if the current node is active (optionally with the desired parameters).
     *
     * @param null $parameters
     * @return string
     */
    public function nodeOrChildIsActive($parameters=null)
    {

        // Check, if this node is active.
        if ($this->isActive($parameters)) {
            return true;
        }

        if ($this->hasChildNodes()) {
            foreach ($this->getChildNodes() as $nodeName => $node) {
                if ($node->nodeOrChildIsActive($parameters)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the page title of this node (defaults to the ucfirst-ified node-name).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @return string
     */
    public function getTitle($parameters=null, $locale=null)
    {
        $title = $this->getData('title',$parameters, $locale);
        return $this->processTitle($parameters, $locale, $title);
    }

    /**
     * Get the page title to be used in navigations (e.g. breadcrumbs or menus) of this node (defaults to the result of $this->getTitle()).
     *
     * @param array $parameters An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the title-generation (default=current route-parameters).
     * @param string $locale The language the title should be fetched for (default=current locale).
     * @return string
     */
    public function getNavTitle($parameters=null, $locale=null)
    {

        // Try retrieving title.
        $title = $this->getData('navTitle',$parameters, $locale);

        // If no title could be determined, we fall back to the result of the $this->getTitle() call.
        if ($title === false) {
            return $this->getTitle($parameters, $locale);
        }

        return $this->processTitle($parameters, $locale, $title);
    }

    /**
     * Processes the value, that was returned as the title.
     *
     * @param $parameters
     * @param $locale
     * @param $title
     * @return array|mixed|string
     */
    public function processTitle($parameters, $locale, $title)
    {
        // If $title is an array, and this node has a parameter, and a requested parameter was handed in $parameters,
        // we return the appropriate value, if the parameter exists as a key within $title,
        // otherwise we just return the handed-over parameter.
        if (is_array($title) && $this->hasParameter()) {

            // If this node or a child node is active, we can try to obtain any missing parameters from the current url.
            if ($this->nodeOrChildIsActive()) {
                $parameters = route_tree()->getCurrentAction()->autoFillPathParameters($parameters, $locale, false);
            }

            if (isset($title[$parameters[$this->getParameter()]])) {
                $title = $title[$parameters[$this->getParameter()]];
            } else {
                $title = $parameters[$this->getParameter()];
            }

        }

        if (is_array($title)) {
            $title = key($title);
        }

        // Per default we just return the upper-cased node-name.
        if ($title === false) {
            $title = ucfirst($this->name);
            return $title;
        }
        return $title;
    }

    /**
     * Get the possible parameter-values and their slugs, if this node is a parameter-node.
     *
     * @param string $language The language the values should be fetched for (default=current locale).
     * @return array
     */
    public function getValues($language=null)
    {
        return $this->getData('values', null, $language);
    }

    /**
     * Checks, if this node is a parameter node and it's parameter is currently active.
     *
     * @return bool
     */
    public function hasActiveParameter() {
        if (!is_null($this->parameter)) {
            return \Route::current()->hasParameter($this->parameter);
        }
        return false;
    }

    /**
     * Get the currently active raw (untranslated/unsluggified) parameter-value.
     *
     * @return string
     */
    public function getActiveValue()
    {

        if ($this->hasActiveParameter()) {

            // We get the currently active value for the parameter of this node.
            $activeValue = \Route::current()->parameter($this->parameter);

            // This might actually be a translated or sluggified version of the real value,
            // so we have to get an array of all values and their translations/slugs and find out the real one.
            $possibleValues = $this->getData('values');

            if (is_array($possibleValues)) {
                $realValue = array_search($activeValue, $possibleValues);

                if ($realValue !== false) {
                    $activeValue = $realValue;
                }
            }

            return $activeValue;

        }

        return false;
    }

    /**
     * Get a (translated version or the slug of a) specific parameter-value.
     *
     * @param $value If no value is stated, the currently active one is determined (if possible).
     * @param string $language The language the value should be fetched for (default=current locale).
     * @return false|string
     */
    public function getValueSlug($value=null, $language=null)
    {
        // If no value was handed over, we try using the current one.
        if (is_null($value)) {
            $value = $this->getActiveValue();
        }

        if (strlen($value)>0) {

            // Fetch the whole list of available values and their translations in the requested language.
            $values = $this->getValues($language);

            // Return the slug for the requested value, if present
            if (isset($values[$value])) {
                return $values[$value];
            }

            // Otherwise we return the currently active value
            return $value;
        }

        return false;
    }

    /**
     * Set custom data.
     * 
     * @param $key
     * @param \Callable|array[]|mixed $data Can be either a callable, an associative array (language => value), or a string/bool (that is should be used for every language).
     * @return RouteNode
     */
    public function setData($key,$data)
    {

        // If $data is callable or already a (language-)array, we set it directly.
        if (is_callable($data) || is_array($data)) {
            $this->data[$key] = $data;
        }
        // In all other cases ($data is a string or bool), we set it's value for each language.
        else {
            foreach (\Config::get('app.locales') as $language => $fullLanguage) {
                $this->data[$key][$language] = $data;
            }
        }

        return $this;
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
    public function getData($key, $parameters=null, $locale=null, $action=null)
    {
        // If no language is specifically stated, we use the current locale
        RouteTree::establishLocale($locale);

        // If no parameters were handed over, we use current route-parameters as default.
        RouteTree::establishRouteParameters($parameters);

        $array_key = $key;
        // If an action was explicitly stated, we use "$key_$action" as the array-key we are looking for.
        if ($action !== null) {
            $array_key = $key.'_'.$action;
        }

        // If this data was specifically set...
        if (isset($this->data[$array_key])) {

            // If data is a callable, we retrieve the data for this language by calling it.
            if (is_callable($this->data[$array_key])) {
                return call_user_func($this->data[$array_key], $parameters, $locale);
            }

            // If data is an array and contains an element for this language, we return that.
            if (is_array($this->data[$array_key]) && isset($this->data[$array_key][$locale])) {
                return $this->data[$array_key][$locale];
            }

        }

        // Try using auto-translation as next option.
        $translationKey = $key.'.'.$this->name;
        // If an action was explicitly stated, we append "_$action" to the translation-key.
        if ($action !== null) {
            $translationKey .= '_'.$action;
        }
        $autoTranslatedValue = $this->performAutoTranslation($translationKey, $parameters, $locale);
        if ($autoTranslatedValue !== false) {
            return $autoTranslatedValue;
        }

        // Per default we return false to indicate no data was found.
        return false;
    }

    /**
     * Tries to auto-translate a stated key into a stated language.
     *
     * @param string $key The translation-key to be translated.
     * @param array $parameters An associative array of [parameterName => parameterValue] that should be passed to the translation (default=current route-parameters).
     * @param string $language The language to be used for translation.
     * @return bool|string
     */
    protected function performAutoTranslation($key, $parameters, $language) {

        // Set the translation key to be used for getting the data.
        $translationKey = $this->langFile.'.'.$key;

        // If a translation for this language exists, we return that as the data.
        if (\Lang::hasForLocale($translationKey, $language)) {
            return trans($translationKey, $parameters, 'messages', $language);
        }

        return false;

    }

    /**
     * Gets the middleware-array fot this node.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Adds multiple middleware from an array to this node.
     *
     * @param array $middlewareArray
     */
    public function addMiddlewareFromArray($middlewareArray = []) {
        foreach ($middlewareArray as $middlewareKey => $middlewareData) {
            if (!isset($middlewareData['parameters'])) {
                $middlewareData['parameters'] = [];
            }
            if (!isset($middlewareData['inherit'])) {
                $middlewareData['inherit'] = true;
            }
            $this->addMiddleware($middlewareKey, $middlewareData['parameters'], $middlewareData['inherit']);
        }
    }

    /**
     * Adds a single middleware to this node.
     *
     * @param string $name Name of the middleware.
     * @param array $parameters Parameters the middleware should be called with.
     * @param bool $inherit Should this middleware be inherited to all child-nodes.
     */
    public function addMiddleware($name='', $parameters=[], $inherit=true) {
        $this->middleware[$name] = [
            'parameters' => $parameters,
            'inherit' => $inherit
        ];
    }

    /**
     * Adds a specific action to this node.
     *
     * @param RouteAction $routeAction
     * @return $this
     */
    public function addAction(RouteAction $routeAction) {

        // Set the RouteNode within the RouteAction.
        $routeAction->setRouteNode($this);

        // Add the action to $this->actions
        $this->actions[$routeAction->getAction()] = $routeAction;

        return $this;
    }

    /**
     * Gets a specific action from this node.
     *
     * @param $action
     * @return bool|RouteAction
     */
    public function getAction($action) {
        if ($this->hasAction($action))  {
            return $this->actions[$action];
        }
        else {
            return false;
        }
    }

    /**
     * Checks if a specific action should be retrieved for this node.
     *
     * @param $action
     * @return bool
     */
    public function hasAction($action) {
        if (isset($this->actions[$action])) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves the paths inherited from this node's parents.
     *
     * @param bool $appendParameter If true, checks, if the parent has a parameter
     * @return array|bool
     */
    protected function getInheritedPaths($appendParameter=false) {

        if ($this->hasParentNode()) {
            if ($this->parentNode->inheritPath) {
                $pathsToInherit = $this->parentNode->paths;
                if ($appendParameter && $this->parentNode->hasParameter()) {
                    foreach ($pathsToInherit as $language => $path) {

                        $pathsToInherit[$language] .= '/{'.$this->parentNode->getParameter().'}';
                    }
                }
                return $pathsToInherit;
            }
            else {
                return $this->parentNode->getInheritedPaths($appendParameter);
            }
        }

        return false;
    }

    /**
     * Sets the path-segments to be used for this node in all languages.
     *
     * @param null $segments
     */
    public function setSegments($segments=null) {

        // Iterate through configured languages.
        foreach (\Config::get('app.locales') as $language => $fullLanguage) {

            // If $segments is an array and contains an entry for this language, we use that.
            if (is_array($segments) && isset($segments[$language])) {
                $this->setSegmentForLanguage($segments[$language], $language);
            }
            // If $segments is a string, we use that.
            else if (is_string($segments)){
                $this->setSegmentForLanguage($segments, $language);
            }

        }

    }

    /**
     * Sets the path-segment to be used for this node in the specified languages.
     *
     * @param $segment
     * @param $language
     */
    protected function setSegmentForLanguage($segment,$language) {

        $this->segments[$language] = $segment;

        // If the path segment is a parameter, we also store it in $this->parameter.
        if ((substr($segment,0,1) === '{') && (substr($segment,-1) === '}')) {
            $this->parameter = str_replace('{','',str_replace('}','',$segment));
        }

    }

    /**
     * Automatically sets all path-segments, that have not yet specifically set.
     * It checks for each-language, if an auto-translation is set,
     * otherwise it uses the node-name as the path-segment.     *
     */
    protected function setAutoSegments() {

        // Set the translation key to be used for getting localized path-segments.
        $segmentTranslationKey = 'segment.'.$this->name;

        // Iterate through configured languages.
        foreach (\Config::get('app.locales') as $language => $fullLanguage) {

            if (!isset($this->segments[$language])) {

                // Standard path segment is the name of this route node.
                $pathSegment = $this->name;

                // If a auto-translation segment for this language exists, we use that as path segment.
                $autoTranslatedSegment = $this->performAutoTranslation($segmentTranslationKey, [], $language);
                if ($autoTranslatedSegment !== false) {
                    $pathSegment = $autoTranslatedSegment;
                }

                $this->setSegmentForLanguage($pathSegment, $language);

            }
        }
    }


    /**
     * Generates the full paths to be used for this node in all languages.
     *
     */
    protected function generateFullPaths() {

        // Get the inherited paths.
        $inheritedPaths = $this->getInheritedPaths($this->isResourceChild);

        // Iterate through configured languages.
        foreach (\Config::get('app.locales') as $language => $fullLanguage) {

            // If an inherited path could be determined, we overtake that.
            if ($inheritedPaths !== false) {
                $this->paths[$language] = $inheritedPaths[$language];
            }
            // If no inherited path could be determined, we start the full-path with the language.
            else {
                $this->paths[$language] = $language;
            }

            // Append the path segment for the current node.
            if (strlen($this->segments[$language]) > 0) {
                $this->paths[$language] .= '/' . $this->segments[$language];
            }
        }
    }

    /**
     * Generates the routes for all actions of this node and it's child-nodes.
     */
    public function generateRoutesOfNodeAndChildNodes() {

        // Make sure, paths for all languages are set.
        $this->setAutoSegments();

        // Generate the full-paths for this node.
        $this->generateFullPaths();

        if ($this->hasChildNodes()) {
            foreach ($this->getChildNodes() as $childNode) {
                $childNode->generateRoutesOfNodeAndChildNodes();
            }
        }

        $this->generateRoutes();

    }

    /**
     * Generate the routes of all actions for this node.
     */
    protected function generateRoutes() {
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
