<?php

namespace Webflorist\RouteTree\Traits;

use Webflorist\RouteTree\RouteTree;

trait HandleLocaleFromUrl
{

    /**
     *Set app locale from url
     *
     * @return string locale
     */
    public function setLocaleFromUrl()
    {
        \App::setLocale(
            $this->validLocaleInUrl() ? $this->getLocaleFromUrl() : \App::getLocale()
        );
    }

    /**
     * Resturn the first segment from request string
     *
     * @return null|string
     */
    public function getLocaleFromUrl()
    {
        return  request()->segment(1);
    }

    /**
     * Check if the first segment is an valid locale
     *
     * @return bool
     */
    public function validLocaleInUrl()
    {
        return array_key_exists($this->getLocaleFromUrl(), RouteTree::getLocales());
    }

}