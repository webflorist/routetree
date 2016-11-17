<?php

namespace Nicat\RouteTree;

use Closure;
use Illuminate\Session\SessionManager;
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
     * @var SessionManager
     */
    private $session;

    /**
     * Create a new throttle middleware instance.
     *
     * @param RouteTree $routeTree
     *
     */
    public function __construct(RouteTree $routeTree, SessionManager $session)
    {
        $this->routeTree = $routeTree;
        $this->session = $session;
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

        //Set current locale default from Session
        if(!$this->session->has('locale')) {
            $this->session->set('locale', \App::getLocale());
        }

        \App::setLocale($this->session->get('locale'));

        // Set current and session locale depending on first path-segment.
        $locale = $request->segment(1);
        
        if ( array_key_exists($locale, \Config::get('app.locales'))) {
            $this->session->set('locale', $locale);
            \App::setLocale($locale);
        }

        // Generate all RouteTree routes.
        $this->routeTree->generateAllRoutes();

        // Try finding out current RouteAction

        // We check, if any route registered with laravel matches the current request,
        // and catch the NotFoundHttpException, if this is not the case.
        try {

            // Try getting the current route.
            $currentRoute = \Route::getRoutes()->match($request);

            // Find out and set the currently active action.
            $currentAction = $this->routeTree->getActionByMethodAndRoute($request->method(), $currentRoute);
            if (is_a($currentAction,RouteAction::class)) {
                $this->routeTree->setCurrentAction($currentAction);
            }


        }
        catch(NotFoundHttpException $exception) {

            // If no route was found, we try to perform an auto-redirect, if current method is 'GET':

            if ($request->method() === 'GET') {

                // If the root of the website was called, we redirect to the language root of the current locale.
                if ($request->path() === '/') {
                    return redirect()->to(\App::getLocale());
                }

                // Otherwise, we try finding an appropriate path
                // using the paths registered with the RouteTree service.
                foreach ($this->routeTree->getRegisteredPathsByMethod('get') as $path => $actions) {
                    if (strpos($path, '/' . $request->path()) !== false) {
                        return redirect()->to($path);
                    }
                }

            }

            // If no auto-redirect occurred, we throw the original exception.
            throw $exception;
        }

        return $next($request);
    }
}
