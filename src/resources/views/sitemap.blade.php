<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($urlset as $registeredRoute)
        <url>
            <loc>{{route($registeredRoute->routeName)}}</loc>
            @if($registeredRoute->routeNode->sitemap->hasLastmod())
                <lastmod>{{$registeredRoute->routeNode->sitemap->getLastmod()}}</lastmod>
            @endif
            @if($registeredRoute->routeNode->sitemap->hasChangefreq())
                <changefreq>{{$registeredRoute->routeNode->sitemap->getChangefreq()}}</changefreq>
            @endif
            @if($registeredRoute->routeNode->sitemap->hasPriority())
                <priority>{{$registeredRoute->routeNode->sitemap->getPriority()}}</priority>
            @endif
        </url>
    @endforeach
</urlset>