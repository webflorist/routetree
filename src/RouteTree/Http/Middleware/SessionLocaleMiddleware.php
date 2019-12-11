<?php

namespace Webflorist\RouteTree\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webflorist\RouteTree\Events\LocaleChanged;
use Webflorist\RouteTree\Events\Redirected;
use Webflorist\RouteTree\Http\Middleware\Traits\DeterminesLocale;
use Webflorist\RouteTree\RegisteredRoute;
use Webflorist\RouteTree\RouteAction;
use Webflorist\RouteTree\RouteTree;

class SessionLocaleMiddleware
{
    use DeterminesLocale;

    /**
     * The RouteTree instance.
     *
     * @var RouteTree
     */
    protected $routeTree;

    /**
     * Laravel's Router.
     *
     * @var Router
     */
    private $router;

    /**
     * Create a new throttle middleware instance.
     *
     * @param RouteTree $routeTree
     * @param Router $router
     */
    public function __construct(RouteTree $routeTree, Router $router)
    {
        $this->routeTree = $routeTree;
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currentRoute = $this->router->getCurrentRoute();
        $oldLocale = session()->get('locale');
        $locale = $this->determineLocale($request, $currentRoute);

        app()->setLocale($locale);

        session()->put('locale', $locale);

        if ($oldLocale !== $locale) {
            event(new LocaleChanged($locale, $oldLocale));
        }

        return $next($request);
    }

}
