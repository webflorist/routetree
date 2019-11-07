<?php

namespace RouteTreeTests;

use Illuminate\Routing\Route;
use Webflorist\RouteTree\Models\RouteActionModel;
use Webflorist\RouteTree\Models\RouteNodeModel;
use Webflorist\RouteTree\RouteTreeServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RouteTreeTests\Feature\Middleware\Test1Middleware;
use RouteTreeTests\Feature\Middleware\Test2Middleware;
use RouteTreeTests\Feature\Middleware\Test3Middleware;
use RouteTreeTests\Feature\Middleware\Test4Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class LegacyTestCase extends BaseTestCase
{

    protected $rootNode = [];

    protected $nodeTree = [];

    protected $expectedResult = [];

    protected $useDatabase = false;

    protected $appConfig = [

        'locale' => 'de',
        'locales' => ['de' => 'Deutsch', 'en' => 'English'],
    ];

    protected $routeTreeConfig = [

        /*
        |--------------------------------------------------------------------------
        | Start Paths with locale?
        |--------------------------------------------------------------------------
        |
        | Set to false, if you don't want paths starting with locale.
        |
        */
        'start_paths_with_locale' => true,

        /*
        |--------------------------------------------------------------------------
        | Create absolute paths instead of relative paths by default?
        |--------------------------------------------------------------------------
        |
        | Can still be overridden with function-parameters.
        |
        */
        'absolute_urls' => true,


        /*
        |--------------------------------------------------------------------------
        | Translation Settings
        |--------------------------------------------------------------------------
        |
        | Here you may configure the settings for the auto-translation
        | functionality of the RouteTree package.
        |
        */
        'localization' => [

            /*
             * The base-folder for translations (optionally including any namespace)
             */
            'base_folder'  => 'RouteTreeTests::pages',

            /*
             * The name of the file, in which auto-translations reside.
             */
            'file_name' => 'pages',

        ],

    ];

    protected function getPackageProviders($app)
    {
        return [RouteTreeServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [];
    }

    /**
     * Setup the test environment.
     */
    public function setUp() :void
    {
        parent::setUp();

        // Load Session
        $this->app['request']->setLaravelSession($this->app['session']->driver('array'));

        // Add Translations
        $this->app['translator']->addNamespace('RouteTreeTests', __DIR__ . "/Feature/lang");

        // Register test-middlewares.
        $this->app['router']->aliasMiddleware('test1', Test1Middleware::class);
        $this->app['router']->aliasMiddleware('test2', Test2Middleware::class);
        $this->app['router']->aliasMiddleware('test3', Test3Middleware::class);
        $this->app['router']->aliasMiddleware('test4', Test4Middleware::class);

        // Migrate Database, if required.
        if ($this->useDatabase) {
            $this->artisan('migrate', ['--database' => 'testbench']);
        }

    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

        // Set app config
        $app['config']->set('app', $this->appConfig);

        // Set routetree config
        $app['config']->set('routetree', $this->routeTreeConfig);

        // Set view config
        $app['config']->set('view.paths', [
            dirname(__FILE__).'/Feature/Views'
        ]);

        // Set database config.
        if ($this->useDatabase) {
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        }

        // Set Test-Route
        //$app['router']->get($this->testRoute, ['uses' => TestController::class.'@test']);

    }

    /**
     * Performs a test against a single uri.
     * @param string $uri
     */
    protected function performSingleUriTest($uri = '')
    {
        $this->setupRouteTree();

        // Visit the uri.
        try {
            $result = json_decode($this->get($uri)->baseResponse->getContent(), true);
        }
        catch(NotFoundHttpException $exception) {
            throw $exception;
        }

        // Sort expected and actual result
        ksort($result);
        ksort($this->expectedResult);

        // Assert, that expected and actual routes-array are equal.
        $this->assertEquals(
            $this->expectedResult,
            $result
        );
    }

    /**
     * Performs a test against all generated routes.
     */
    protected function performFullRoutesTest()
    {

        // Setup Route-Tree
        $this->setupRouteTree();

        // Visit the root
        $this->get('');

        // Accumulate all routes
        $routes = [];
        foreach (\Route::getRoutes() as $route) {
            /** @var Route $route */
            $method = str_replace('|HEAD', '', implode('|', $route->methods()));
            $uri = $route->uri();
            $routes[$route->getName()] = [
                'method' => $method,
                'uri' => $uri,
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'content' => json_decode($this->call($method, $uri)->baseResponse->getContent(), true)
            ];
        }

        // Sort expected and actual routes-array by key
        ksort($routes);
        ksort($this->expectedResult);

        // Assert, that expected and actual routes-array are qequal.
        $this->assertEquals(
            $this->expectedResult,
            $routes
        );
    }

    /**
     * Override 'call' to follow redirects and throw exceptions.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        if ($response->isRedirection()) {
            $response = $this->call($method, $response->baseResponse->headers->get('Location'), $parameters, $cookies, $files, $server, $content);
        }

        if (!is_null($response->exception)) {
            throw $response->exception;
        }
        return $response;
    }

    protected function setupRouteTree(): void
    {

        // Set root-node.
        route_tree()->setRootNode($this->rootNode);

        if ($this->useDatabase) {
            foreach ($this->nodeTree as $nodeName => $nodeData) {
                $this->insertNodeIntoDb($nodeName, $nodeData);
            }
            route_tree()->loadFromDb();
        }
        else {

            // Set nodes.
            route_tree()->addNodes($this->nodeTree);

        }
    }

    private function insertNodeIntoDb(string $nodeName, array $nodeData, int $parentId=null)
    {
        $routeNode = new RouteNodeModel;
        $routeNode->name = $nodeName;
        $routeNode->parent_id = $parentId;

        $actions = [];
        $data = [];

        // We traverse each key set in $nodeData and perform the needed tasks on $routeNode.
        foreach ($nodeData as $key => $value) {
            switch($key) {
                case 'segment':
                    $routeNode->segments = $value;
                    break;
                case 'middleware':
                    $routeNode->middleware = $value;
                    break;
                case 'namespace':
                    $routeNode->namespace = $value;
                    break;
                case 'inheritPath':
                    $routeNode->inherit_path = $value;
                    break;
                case 'index':
                case 'create':
                case 'store':
                case 'show':
                case 'edit':
                case 'update':
                case 'destroy':
                case 'get':
                case 'post':
                    $actions[$key] = $value;
                    break;
                case 'resource':
                    // Todo
                    break;
                default:
                    $data[$key] = $value;
            }
        }

        if (count($data)>0) {
            $routeNode->data = $data;
        }

        $routeNode->save();

        if (count($actions)>0) {
            foreach ($actions as $actionName => $actionData) {
                $this->insertActionIntoDb($actionName, $actionData, $routeNode->id);
            }
        }

        if (isset($nodeData['children'])) {
            foreach($nodeData['children'] as $childName => $childData) {
                $this->insertNodeIntoDb($childName, $childData, $routeNode->id);
            }
        }
    }

    private function insertActionIntoDb(string $actionName, array $actionData, int $nodeId)
    {
        $routeAction = new RouteActionModel;

        $actionType = null;
        $actionValue = null;
        $middleware = null;

        if (isset($actionData['closure'])) {
            $actionType = 'closure';
            $actionValue = $actionData['closure'];
        }
        else if (isset($actionData['view'])) {
            $actionType = 'view';
            $actionValue = $actionData['view'];
        }
        else if (isset($actionData['redirect'])) {
            $actionType = 'redirect';
            $actionValue = $actionData['redirect'];
        }
        else if (isset($actionData['uses'])) {
            $actionType = 'uses';
            $actionValue = $actionData['uses'];
        }

        if (isset($actionData['middleware'])) {
            $middleware = $actionData['middleware'];
        }

        $routeAction->node_id = $nodeId;
        $routeAction->name = $actionName;
        $routeAction->type = $actionType;
        $routeAction->value = $actionValue;
        $routeAction->middleware = $middleware;

        $routeAction->save();
    }



    protected function generateTestRoutes($visitUri='') {

        route_tree()->setRootNode([
            'namespace' => 'RouteTreeTests\Feature\Controllers',
            'index' => ['uses' => 'TestController@get'],
            'children' => [
                'page1' => [
                    'index' => ['uses' => 'TestController@get'],
                    'children' => [
                        'page1-1' => [
                            'index' => ['uses' => 'TestController@get'],
                        ]
                    ]
                ]
            ]
        ]);

        // Visit the uri.
        try {
            json_decode($this->get($visitUri)->baseResponse->getContent(), true);
        }
        catch(NotFoundHttpException $exception) {
            throw $exception;
        }

    }


}
