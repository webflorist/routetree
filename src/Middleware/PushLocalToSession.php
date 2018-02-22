<?php

namespace Nicat\RouteTree\Middleware;

use Closure;
use Illuminate\Session\SessionManager;
use Nicat\RouteTree\RouteTree;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class PushLocalToSession
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
        //Set current locale default from Session
        if(!session()->has('locale')) {
            session()->put('locale', \App::getLocale());
        }

        \App::setLocale(session()->get('locale'));

        // Set current and session locale depending on first path-segment.
        $locale = $request->segment(1);

        if ( array_key_exists($locale, \Config::get('app.locales'))) {
            session()->put('locale', $locale);
            \App::setLocale($locale);
        }

        return $next($request);
    }
}
