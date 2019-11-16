<?php

namespace Webflorist\RouteTree\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
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

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Throwable
     */
    public function handle()
    {

        file_put_contents(
            $this->getOutputFile(),
            view('webflorist-routetree::sitemap',[
                'urlset' => $this->getUrlset()
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

    private function getUrlset()
    {
        return route_tree()->getRegisteredRoutesByMethod('get')->filter(function (RegisteredRoute $registeredRoute) {

            if ($registeredRoute->routeNode->sitemap->isExcluded()) {
                $this->warn($registeredRoute->path." EXCLUDED manually.");
                return false;
            }

            if ($registeredRoute->routeAction->hasParameters()) {
                $this->warn($registeredRoute->path." EXCLUDED due to parameters.");
                return false;
            }

            if ($registeredRoute->routeAction->isRedirect()) {
                $this->warn($registeredRoute->path." EXCLUDED due to redirect.");
                return false;
            }

            foreach ($this->getExcludedMiddleware() as $excludedMiddleware) {
                if ($registeredRoute->routeAction->hasMiddleware($excludedMiddleware)) {
                    $this->warn($registeredRoute->path." EXCLUDED due to use of middleware '$excludedMiddleware'.");
                    return false;
                }
            }

            $this->info($registeredRoute->path." INCLUDED.");

            return true;
        });
    }

    private function getExcludedMiddleware()
    {
        return [
            'auth'
        ];
    }

}
