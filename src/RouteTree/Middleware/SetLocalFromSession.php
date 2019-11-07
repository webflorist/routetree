<?php

namespace Webflorist\RouteTree\Middleware;

use Closure;
use Illuminate\Session\SessionManager;
use Webflorist\RouteTree\RouteTree;
use Webflorist\RouteTree\Traits\HandleLocaleFromUrl;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class SetLocalFromSession
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
        //check if we have no session locale, and set a default
        if (!session()->has('locale')) {
            session()->put('locale', \App::getLocale());
        }

        //on valid locale in url let override session
        if ($this->validLocaleInUrl()) {
            session()->put('locale', $this->getLocaleFromUrl());
        }

        //Set locale from Session
        \App::setLocale(session()->get('locale'));

        return $next($request);
    }
}
