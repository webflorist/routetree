<?php

namespace Webflorist\RouteTree\Interfaces;

/**
 * Interface ProvidesRoutePayload
 *
 * An Eloquent Model stated with a RouteParameter or RouteResource can implement this interface,
 * to provide custom route-related data (e.g. page title) for parameter nodes.
 *
 * This can be used to e.g. retrieve Blog-Titles as page-titles from the RouteAction.
 *
 * @package Webflorist\RouteTree
 */
interface ProvidesRoutePayload
{
    /**
     * Should return a route payload value.
     *
     * @param string $payloadKey The payload-key (e.g. title, navTitle, lastmod, whatever).
     * @param array|null $parameters Array of route parameters to determine the correct values.
     * @param string|null $locale The language to retrieve the payload for.
     * @param string|null $action The action (e.g. index, show, edit, create, get) to retrieve the payload for.
     * @return mixed
     */
    public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action);
}