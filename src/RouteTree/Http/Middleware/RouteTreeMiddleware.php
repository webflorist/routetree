<?php

namespace Webflorist\RouteTree\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webflorist\RouteTree\Events\Redirected;
use Webflorist\RouteTree\Http\Middleware\Traits\DeterminesLocale;
use Webflorist\RouteTree\RegisteredRoute;
use Webflorist\RouteTree\RouteAction;
use Webflorist\RouteTree\RouteTree;

class RouteTreeMiddleware
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
        // If routes are cached, we load the RouteTree from cache.
        if (app()->routesAreCached()) {
            $this->routeTree->loadCachedRouteTree();
        }
        // Otherwise we make sure, all routes are generated.
        else {
            $this->routeTree->generateAllRoutes();
        }

        // Try getting the current route.
        $currentRoute = $this->getCurrentRoute($request);

        // Find out and set the currently active action.
        $this->setCurrentAction($currentRoute);

        // Determine and set current locale
        app()->setLocale($this->determineLocale($request, $currentRoute));

        // If no route could be matched,
        // we might be able to redirect the user to
        // a corresponding resource.
        if (is_null($currentRoute)) {
            $redirectLocation = $this->determineRedirect($request, $currentRoute);
            if (!is_null($redirectLocation)) {
                event(new Redirected($request->getPathInfo(), $redirectLocation));
                return redirect()->to($redirectLocation);
            }
        }

        return $next($request);
    }

    /**
     * Tries to match the current request to a Laravel Route.
     *
     * @param $request
     * @return Route|null
     */
    protected function getCurrentRoute($request)
    {
        try {
            return $this->router->getRoutes()->match($request);
        } catch (NotFoundHttpException $exception) {
            return null;
        }
    }

    /**
     * Set's RouteTree's current action.
     *
     * @param Route|null $currentRoute
     */
    protected function setCurrentAction(?Route $currentRoute): void
    {
        if (!is_null($currentRoute)) {
            $currentAction = $this->routeTree->getActionByRoute($currentRoute);

            if (is_a($currentAction, RouteAction::class)) {
                $this->routeTree->setCurrentAction($currentAction);
            }
        }
    }


    /**
     * Determine, if a redirect should take place.
     *
     * @param Request $request
     * @param Route|null $currentRoute
     * @return string|null
     */
    private function determineRedirect(Request $request, ?Route $currentRoute)
    {
        // We only do redirects for GET requests.
        if ($request->method() !== 'GET') {
            return null;
        }

        // If the root of the website was called, we redirect to the language root of the current locale.
        $requestPath = $request->path();
        if (($requestPath === '/') || ($requestPath === '')) {
            return app()->getLocale();
        }

        // Otherwise, we try finding an appropriate path of any language
        // using the paths registered with the RouteTree service.
        $foundPath = null;
        $this->routeTree->getRegisteredRoutesByMethod('get')->each(function (RegisteredRoute $registeredRoute) use (&$foundPath, $request) {
            if (strpos($registeredRoute->path, $registeredRoute->locale . '/' . $request->path()) === 0) {
                $foundPath = $registeredRoute->path;
                return false;
            }
        });
        return $foundPath;
    }
}
