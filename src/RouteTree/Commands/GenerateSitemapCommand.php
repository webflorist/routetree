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

        $this->info("Package Blueprint command successful.");
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
            return !$registeredRoute->routeNode->sitemap->isExcluded();
        });
    }

}
