<?php

namespace Webflorist\RouteTree\Services;

use Illuminate\Console\Command;
use Webflorist\RouteTree\RegisteredRoute;

/**
 * Class XmlSitemapGenerator
 *
 * This class is used to generate XML-sitemaps.
 *
 * @package Webflorist\RouteTree
 */
class XmlSitemapGenerator
{

    /**
     * The generated Urlset for the sitemap.
     *
     * @var array
     */
    private $urlset = [];

    public function __construct(?Command $artisanCmd = null)
    {
        $this->artisanCmd = $artisanCmd;
    }

    public function generate()
    {
        $this->generateUrlset();
        return view('webflorist-routetree::sitemap', [
            'urlset' => $this->urlset
        ])->render();
    }

    /**
     * Generates Urlset from Routes registered with RouteTree.
     */
    private function generateUrlset()
    {
        route_tree()->getRegisteredRoutes(true)->each(function (RegisteredRoute $registeredRoute, $index) {
            if (!$registeredRoute->hasMethod('GET')) {
                $this->warn($registeredRoute->path . " EXCLUDED due to not being GET route.");
                return;
            }

            if ($registeredRoute->routeNode->sitemap->isExcluded()) {
                $this->warn($registeredRoute->path . " EXCLUDED manually.");
                return;
            }

            if (is_null($registeredRoute->routeKeys) && $registeredRoute->routeAction->hasParameters() && !$registeredRoute->routeAction->canResolveAllRouteKeys($registeredRoute->locale)) {
                $this->warn($registeredRoute->path . " EXCLUDED due to parameters without stated values or model-binding.");
                return;
            }

            if ($registeredRoute->routeAction->isRedirect()) {
                $this->warn($registeredRoute->path . " EXCLUDED due to redirect.");
                return;
            }

            foreach ($this->getExcludedMiddleware() as $excludedMiddleware) {
                if ($registeredRoute->routeAction->hasMiddleware($excludedMiddleware)) {
                    $this->warn($registeredRoute->path . " EXCLUDED due to use of middleware '$excludedMiddleware'.");
                    return;
                }
            }

            $this->addRegisteredRouteToUrlset($registeredRoute);

        });
    }

    /**
     * Routes using these middleware
     * will be excluded from the sitemap.
     *
     * @return array
     */
    private function getExcludedMiddleware()
    {
        return config('routetree.sitemap.excluded_middleware');
    }

    /**
     * Parses a RegisteredRoute object and adds it to $this->urlset.
     *
     * @param RegisteredRoute $registeredRoute
     */
    function addRegisteredRouteToUrlset(RegisteredRoute $registeredRoute): void
    {
        $baseUrl = config('routetree.sitemap.base_url');

        // Make sure $baseUrl ends with a slash.
        if (substr($baseUrl, -1) !== '/') {
            $baseUrl .= '/';
        }

        $path = $registeredRoute->path;

        // Make sure $path does not start with a slash.
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }

        $urlData = ['loc' => $baseUrl . $path];
        if ($registeredRoute->routeNode->sitemap->hasLastmod($registeredRoute->routeKeys, $registeredRoute->locale)) {
            $urlData['lastmod'] = $registeredRoute->routeNode->sitemap->getLastmod($registeredRoute->routeKeys, $registeredRoute->locale);
        }
        if ($registeredRoute->routeNode->sitemap->hasChangefreq($registeredRoute->routeKeys, $registeredRoute->locale)) {
            $urlData['changefreq'] = $registeredRoute->routeNode->sitemap->getChangefreq($registeredRoute->routeKeys, $registeredRoute->locale);
        }
        if ($registeredRoute->routeNode->sitemap->hasPriority($registeredRoute->routeKeys, $registeredRoute->locale)) {
            $urlData['priority'] = $registeredRoute->routeNode->sitemap->getPriority($registeredRoute->routeKeys, $registeredRoute->locale);
        }
        $this->urlset[] = $urlData;
        $this->info($urlData['loc'] . " INCLUDED.");
    }

    /**
     * Write a string as warning output
     * to $this->artisanCmd.
     *
     * @param string $string
     * @return void
     */
    public function warn($string)
    {
        if ($this->artisanCmd !== null) {
            $this->artisanCmd->warn($string);
        }
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     * @return void
     */
    public function info($string)
    {
        if ($this->artisanCmd !== null) {
            $this->artisanCmd->info($string);
        }
    }
}
