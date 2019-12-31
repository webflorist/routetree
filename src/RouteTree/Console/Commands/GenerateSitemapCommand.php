<?php

namespace Webflorist\RouteTree\Console\Commands;

use Illuminate\Console\Command;
use Throwable;
use Webflorist\RouteTree\RegisteredRoute;

class GenerateSitemapCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routetree:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a sitemap.xml.';

    /**
     * The generated Urlset for the sitemap.
     *
     * @var array
     */
    private $urlset = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Throwable
     */
    public function handle()
    {
        $this->generateUrlset();

        file_put_contents(
            $this->getOutputFile(),
            view('webflorist-routetree::sitemap', [
                'urlset' => $this->urlset
            ])
                ->render()
        );

        $this->info("Sitemap XML successfully generated at:");
        $this->line($this->getOutputFile());
    }

    /**
     * Get location of output file.
     *
     * @return string
     */
    protected function getOutputFile(): string
    {
        return app()->basePath() . '/' . config('routetree.sitemap.output_file');
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
        return [
            'auth'
        ];
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
        if (substr($path,0,1) === '/') {
            $path = substr($path,1);
        }

        $urlData = ['loc' => $baseUrl . $path];
        if ($registeredRoute->routeNode->sitemap->hasLastmod()) {
            $urlData['lastmod'] = $registeredRoute->routeNode->sitemap->getLastmod();
        }
        if ($registeredRoute->routeNode->sitemap->hasChangefreq()) {
            $urlData['changefreq'] = $registeredRoute->routeNode->sitemap->getChangefreq();
        }
        if ($registeredRoute->routeNode->sitemap->hasPriority()) {
            $urlData['priority'] = $registeredRoute->routeNode->sitemap->getPriority();
        }
        $this->urlset[] = $urlData;
        $this->info($urlData['loc'] . " INCLUDED.");
    }

}
