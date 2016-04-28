<?php

namespace Nicat\RouteTree;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RouteTreeMiddleware
{
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

        // Generate all RouteTree routes.
        $this->routeTree->generateAllRoutes();

        // Handle auto-redirects for GET-requests.
        if ($request->method() === 'GET') {

            // We check, if any route registered with laravel matches the current request,
            // and catch the NotFoundHttpException, if this is not the case.
            try {

                // Try getting the current route.
                $currentRoute = \Route::getRoutes()->match($request);

                // Find out and set the currently active node.
                $this->routeTree->setCurrentNode(
                    $this->routeTree->getNodeByRouteName($currentRoute->getName())
                );

            }
            catch(NotFoundHttpException $exception) {

                // If no route was found, we try to perform an auto-redirect:

                // If the root of the website was called, we redirect to the language root of the current locale.
                if ($request->path() === '/') {
                    return redirect()->to(\App::getLocale());
                }

                // Otherwise, we try finding an appropriate path
                // using the paths registered with the RouteTree service.
                foreach ($this->routeTree->getRegisteredPathsByMethod('get') as $path => $actions) {
                    if (strpos($path,'/'.$request->path()) !== false) {
                        return redirect()->to($path);
                    }
                }

                // If no auto-redirect occurred, we throw the original exception.
                throw $exception;
            }
            
        }

        //dd(\Route::getRoutes());
        //dd($this->routeTree);

        return $next($request);
    }
}