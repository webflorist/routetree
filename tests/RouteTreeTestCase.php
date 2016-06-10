<?php

namespace RouteTreeTests;

use Illuminate\Foundation\Console\RouteListCommand;
use Nicat\RouteTree\RouteTreeServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class RouteTreeTestCase extends TestCase
{

    protected $testRoute = 'test';

    protected $appConfig = [

        'locale' => 'de',
        'locales' => ['de' => 'Deutsch', 'en' => 'English'],
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
    public function setUp()
    {
        parent::setUp();

        // Load Session
        $this->app['request']->setSession($this->app['session']->driver('array'));

        // Add Translations
        $this->app['translator']->addNamespace('ExtendedValidationTests', __DIR__ . "/lang");

    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

        // Set Config
        $app['config']->set('app', $this->appConfig);

        // Set Test-Route
        //$app['router']->get($this->testRoute, ['uses' => TestController::class.'@test']);

    }

}
