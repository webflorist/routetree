<?php

namespace Nicat\RouteTree\Traits;

/**
 * Created by NIC.at GmbH.
 * User: marioo
 * Date: 22.02.2018
 * Time: 13:41
 */
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
        return array_key_exists($this->getLocaleFromUrl(), \Config::get('app.locales'));
    }

}