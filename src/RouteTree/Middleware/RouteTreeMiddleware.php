<?php

namespace Webflorist\RouteTree\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Webflorist\RouteTree\Domain\RegisteredRoute;
use Webflorist\RouteTree\Domain\RouteAction;
use Webflorist\RouteTree\RouteTree;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteTreeMiddleware
{

    /**
     * The RouteTree instance.
     *
     * @var RouteTree
     */
    protected $routeTree;

    /**
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
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // Generate all RouteTree routes.
        $this->routeTree->generateAllRoutes();

        // Try getting the current route.
        $currentRoute = $this->getCurrentRoute($request);

        // Find out and set the currently active action.
        $this->setCurrentAction($currentRoute);

        // Set an appropriate locale.
        $this->setLocale($request, $currentRoute);

        // If no route could be matched,
        // we might be able to redirect the user to
        // a corresponding resource.
        if (is_null($currentRoute)) {
            $redirectLocation = $this->determineRedirect($request);
            if (!is_null($redirectLocation)) {
                return redirect()->to($redirectLocation);
            }
        }

        return $next($request);
    }

    /**
     * @param $request
     * @return Route|null
     */
    protected function getCurrentRoute($request)
    {
        try {
            return $this->router->getRoutes()->match($request);
        }
        catch(NotFoundHttpException $exception) {
            return null;
        }
    }

    /**
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
     * Check if $locale is a valid locale.
     *
     * @param string $locale
     * @return bool
     */
    private function isValidLocale(string $locale)
    {
        return array_search($locale, RouteTree::getLocales()) !== false;
    }

    private function determineLocale(Request $request, ?Route $currentRoute)
    {
        // First try getting locale from first part of the current route name,
        // if a currentRoute was determined.
        if (!is_null($currentRoute)) {
            $firstRouteNameSegment = explode('.',$currentRoute->getName())[0];
            if ($this->isValidLocale($firstRouteNameSegment)) {
                return $firstRouteNameSegment;
            }
        }

        // Try getting locale from session next.
        if (session()->has('locale')) {
            return session()->get('locale');
        }

        // If a HTTP_ACCEPT_LANGUAGE header was sent by the client,
        // we use that.
        if (!is_null($acceptLanguage = $request->header('accept-language'))) {
            foreach (explode(',',$acceptLanguage) as $acceptedLocale) {
                if ($this->isValidLocale($acceptedLocale)) {
                    return $acceptedLocale;
                }
            }
        }

        return config('app.locale');

    }

    private function determineRedirect(\Illuminate\Http\Request $request)
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

    private function setLocale(\Illuminate\Http\Request $request,?Route $currentRoute)
    {
        $locale = $this->determineLocale($request, $currentRoute);
        app()->setLocale($locale);
        session()->put('locale', $locale);
    }
}
