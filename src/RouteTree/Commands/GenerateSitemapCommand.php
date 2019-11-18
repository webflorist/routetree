<?php

namespace Webflorist\RouteTree\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Throwable;
use Webflorist\RouteTree\Domain\RegisteredRoute;
use Webflorist\RouteTree\Domain\RouteAction;

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
            view('webflorist-routetree::sitemap',[
                'urlset' => $this->urlset
            ])
                ->render()
        );

        $this->info("Sitemap XML successfully generated at:");
        $this->line($this->getOutputFile());
    }

    /**
     * @return string
     */
    protected function getOutputFile(): string
    {
        return app()->basePath() . '/' . config('routetree.sitemap.output_file');
    }

    private function generateUrlset()
    {
        route_tree()->getRegisteredRoutesByMethod('get')->each(function (RegisteredRoute $registeredRoute, $index) {

            if ($registeredRoute->routeNode->sitemap->isExcluded()) {
                $this->warn($registeredRoute->path." EXCLUDED manually.");
                return;
            }

            if ($registeredRoute->routeAction->hasParameters() && !$registeredRoute->routeAction->hasParameterValues($registeredRoute->locale)) {
                $this->warn($registeredRoute->path." EXCLUDED due to parameters without stated values or model-binding.");
                return;
            }

            if ($registeredRoute->routeAction->isRedirect()) {
                $this->warn($registeredRoute->path." EXCLUDED due to redirect.");
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

    private function getExcludedMiddleware()
    {
        return [
            'auth'
        ];
    }

    /**
     * @param RegisteredRoute $registeredRoute
     * @param array|null $parameters
     */
    function addRegisteredRouteToUrlset(RegisteredRoute $registeredRoute, ?array $parameters=null): void
    {
        if ($registeredRoute->routeAction->hasParameters() && is_null($parameters)) {
            $this->processParameterValues($registeredRoute);
            return;
        }

        $urlData = ['loc' => config('routetree.sitemap.base_url').route($registeredRoute->routeName, $parameters, false)];
        if($registeredRoute->routeNode->sitemap->hasLastmod()) {
            $urlData['lastmod'] = $registeredRoute->routeNode->sitemap->getLastmod();
        }
        if($registeredRoute->routeNode->sitemap->hasChangefreq()) {
            $urlData['changefreq'] = $registeredRoute->routeNode->sitemap->getChangefreq();
        }
        if($registeredRoute->routeNode->sitemap->hasPriority()) {
            $urlData['priority'] = $registeredRoute->routeNode->sitemap->getPriority();
        }
        $this->urlset[] = $urlData;
        $this->info($urlData['loc']." INCLUDED.");
    }

    private function processParameterValues(RegisteredRoute $registeredRoute)
    {
        $parameterSets = [];
        foreach ($registeredRoute->routeNode->getRootLineParameters() as $routeParameter) {
            foreach ($routeParameter->getValues($registeredRoute->locale) as $value) {
                $parameterSets[] = [
                    $routeParameter->getName() => $value
                ];
            }
        }

        foreach ($parameterSets as $parameterSet) {
            $this->addRegisteredRouteToUrlset(
                $registeredRoute,
                $parameterSet
            );
        }
    }

}
