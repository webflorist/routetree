<?php

namespace Webflorist\RouteTree\Interfaces;

/**
 * Interface ProvidesRouteKeyList
 *
 * An Eloquent Model stated with a RouteParameter or RouteResource can implement this interface,
 * to return an array of all route-keys this parameter can have as values.
 *
 * See ProvidesRouteKeyListDefault for a default implementation.
 *
 * This is used by the sitemap generator and API to
 * acquire a list of all possible URLs.
 *
 * @package Webflorist\RouteTree
 */
interface ProvidesRouteKeyList
{
    /**
     * Should return an array of all possible route-keys for the parameter this Model is associated with.
     *
     * @param string|null $locale The language to retrieve the list for.
     * @param array|null $parameters Array of route parameters, that can be used to determine the correct values (e.g. if blog-articles having a category).
     * @return array
     */
    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): array;
}