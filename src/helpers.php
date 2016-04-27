<?php

use Nicat\RouteTree\RouteTree;

if (! function_exists('routeTree')) {
    /**
     * Get the available auth instance.
     *
     * @return \Nicat\RouteTree\RouteTree
     */
    function routeTree()
    {
        return app(RouteTree::class);
    }
}


if ( ! function_exists('route_locale()')) {
    /**
     * Generate a URL to a named route.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    function route_locale($name, $parameters = [], $absolute = true, $route = null)
    {
        //append a dot(.) before $name and replace double dots(..) with one(.)
        $name = str_replace('..', '.', (app()->getLocale() . '.' . $name));
        return route($name, $parameters, $absolute, $route);
    }
}

if ( ! function_exists('route_name()')) {
    /**
     * Give the current Route name
     *
     * @return string
     */
    function route_name()
    {
        return \Request::route()->getName();
    }
}

if (! function_exists('trans_by_route')) {

    /**
     * Translate the given message and work with current route.
     *
     * @param  string $id
     * @param bool $removeSubLevel
     * @param string $route
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return bool|\Illuminate\Translation\Translator|string|\Symfony\Component\Translation\TranslatorInterface
     */
    function trans_by_route($id = null, $removeSubLevel = false, $route = '', $parameters = [], $domain = 'messages', $locale = null)
    {
        if(empty($route))
            $route = route_name();
        //split route name to array
        $currentRoute = explode('.', $route);
        $lengthOfRoute = count($currentRoute);

        //check if we have an valid route min 1
        if($lengthOfRoute < 1) {
            return false;
        }
        //remove locale prefix from route
        if (array_key_exists($currentRoute[0], config('app.locales'))) {
            array_shift( $currentRoute  );
            $lengthOfRoute--;
        }
        //remove index,show,create,edit suffix
        if (preg_match('/(index|show|create|edit)/', $currentRoute[$lengthOfRoute -1])) {
            array_pop( $currentRoute );
            $lengthOfRoute--;
        }

        //check if we have after strip meta values again a valid route
        if($lengthOfRoute < 1) {
            return false;
        }

        if ($removeSubLevel) {
            $currentRoute = array_slice($currentRoute, 0, -1);
            $lengthOfRoute--;
        }

        //transform currentroute array to strin
        $currentRoute = 'pages/' . implode('/' , $currentRoute);

        $id = $currentRoute . '.' . $id;


        //dd($id);
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}