<?php

namespace Nicat\RouteTree;

use Closure;
use Illuminate\Session\SessionManager;
use Nicat\RouteTree\Traits\HandleLocaleFromUrl;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class RouteTreeMiddleware
{

    use HandleLocaleFromUrl;

    /**
     * The RouteTree instance.
     *
     * @var RouteTree
     */
    protected $routeTree;

    /**
     * Create a new throttle middleware instance.
     *
     * @param RouteTree $routeTree
     *
     */
    public function __construct(RouteTree $routeTree)
    {
        $this->routeTree = $routeTree;
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
        // Set current locale depending on first path-segment.
        $this->setLocaleFromUrl();

        // Generate all RouteTree routes.
        $this->routeTree->generateAllRoutes();

        // Try finding out current RouteAction

        // We check, if any route registered with laravel matches the current request,
        // and catch the NotFoundHttpException, if this is not the case.
        try {

            // Try getting the current route.
            $currentRoute = \Route::getRoutes()->match($request);

            // Find out and set the currently active action.
            $currentAction = $this->routeTree->getActionByRoute($currentRoute);

            if (is_a($currentAction,RouteAction::class)) {
                $this->routeTree->setCurrentAction($currentAction);
            }


        }
        catch(\Exception $exception) {

            // If no route was found, we try to perform an auto-redirect, if current method is 'GET'.
            if (($exception instanceof NotFoundHttpException) && ($request->method() === 'GET')) {

                // If the root of the website was called, we redirect to the language root of the current locale.
                if ($request->path() === '/') {
                    return redirect()->to(\App::getLocale());
                }

                // Otherwise, we try finding an appropriate path of any language
                // using the paths registered with the RouteTree service.
                $foundPath = null;
                $this->routeTree->getRegisteredRoutesByMethod('get')->each(function ($routeData) use (&$foundPath, $request) {
                    if (strpos($routeData['path'], $routeData['language'] . '/' . $request->path()) === 0) {
                        $foundPath = $routeData['path'];
                        return false;
                    }
                });
                if (!is_null($foundPath)) {
                    return redirect()->to($foundPath);
                }


            }

        }

        return $next($request);
    }
}
