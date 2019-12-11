<?php

namespace Webflorist\RouteTree\Http\Middleware\Traits;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Webflorist\RouteTree\RouteTree;

trait DeterminesLocale
{

    /**
     * Determines locale using the following fallback:
     *
     * 1. From the first name-segment of a RouteTree generated Route. (e.g. "en.company.news.get").
     * 2. From a (previously saved) session value (if Session is currently initialized).
     * 3. From a HTTP_ACCEPT_LANGUAGE header sent by the client.
     * 4. From config('app.locale').
     *
     * @param Request $request
     * @param Route|null $currentRoute
     * @return \Illuminate\Config\Repository|mixed
     */
    private function determineLocale(Request $request, ?Route $currentRoute)
    {
        // First try getting locale from first part of the current route name,
        // if a currentRoute was determined.
        if (!is_null($currentRoute)) {
            $firstRouteNameSegment = explode('.', $currentRoute->getName())[0];
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
            foreach (explode(',', $acceptLanguage) as $acceptedLocale) {
                if ($this->isValidLocale($acceptedLocale)) {
                    return $acceptedLocale;
                }
            }
        }

        return config('app.locale');

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
}