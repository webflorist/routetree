<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\Domain\RouteNode;

class ResourceTest extends TestCase
{

    public function test_full_resource()
    {

        $this->routeTree->node('photos', function (RouteNode $node) {
            $node->resource('photo', '\RouteTreeTests\Feature\Controllers\TestController');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.photos.index" => [
                "method" => "GET",
                "uri" => "de/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos',
                    'title' => 'Fotos',
                    'navTitle' => 'Fotos',
                    'h1Title' => 'Fotos',
                ],
            ],
            "en.photos.index" => [
                "method" => "GET",
                "uri" => "en/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos',
                    'title' => 'Photos',
                    'navTitle' => 'Photos',
                    'h1Title' => 'Photos',
                ],
            ],
            "de.photos.create" => [
                "method" => "GET",
                "uri" => "de/photos/erstellen",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos/erstellen',
                    'title' => 'Foto erstellen',
                    'navTitle' => 'Erstellen',
                    'h1Title' => 'Foto erstellen',
                ],
            ],
            "en.photos.create" => [
                "method" => "GET",
                "uri" => "en/photos/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos/create',
                    'title' => 'Create Photo',
                    'navTitle' => 'Resource',
                    'h1Title' => 'Create Photo',
                ],
            ],
            "de.photos.store" => [
                "method" => "POST",
                "uri" => "de/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@store',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'locale' => 'de',
                    'path' => 'de/photos',
                    'title' => 'Fotos',
                    'navTitle' => 'Fotos',
                    'h1Title' => 'Fotos',
                ],
            ],
            "en.photos.store" => [
                "method" => "POST",
                "uri" => "en/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@store',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'locale' => 'en',
                    'path' => 'en/photos',
                    'title' => 'Photos',
                    'navTitle' => 'Photos',
                    'h1Title' => 'Photos',
                ],
            ],
            "de.photos.edit" => [
                "method" => "GET",
                "uri" => "de/photos/{photo}/bearbeiten",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos/{photo}/bearbeiten',
                    'title' => 'Foto bearbeiten',
                    'navTitle' => 'Bearbeiten',
                    'h1Title' => 'Foto bearbeiten',
                ],
            ],
            "en.photos.edit" => [
                "method" => "GET",
                "uri" => "en/photos/{photo}/edit",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos/{photo}/edit',
                    'title' => 'Edit Photo',
                    'navTitle' => 'Edit',
                    'h1Title' => 'Edit Photo',
                ],
            ],
            "de.photos.update" => [
                "method" => "PUT",
                "uri" => "de/photos/{photo}",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@update',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'update',
                    'method' => 'PUT',
                    'locale' => 'de',
                    'path' => 'de/photos/{photo}',
                    'title' => 'Fotos',
                    'navTitle' => 'Fotos',
                    'h1Title' => 'Fotos',
                ],
            ],
            "en.photos.update" => [
                "method" => "PUT",
                "uri" => "en/photos/{photo}",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@update',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'update',
                    'method' => 'PUT',
                    'locale' => 'en',
                    'path' => 'en/photos/{photo}',
                    'title' => 'Photos',
                    'navTitle' => 'Photos',
                    'h1Title' => 'Photos',
                ],
            ],
            "de.photos.destroy" => [
                "method" => "DELETE",
                "uri" => "de/photos/{photo}",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'destroy',
                    'method' => 'DELETE',
                    'locale' => 'de',
                    'path' => 'de/photos/{photo}',
                    'title' => 'Fotos',
                    'navTitle' => 'Fotos',
                    'h1Title' => 'Fotos',
                ],
            ],
            "en.photos.destroy" => [
                "method" => "DELETE",
                "uri" => "en/photos/{photo}",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'destroy',
                    'method' => 'DELETE',
                    'locale' => 'en',
                    'path' => 'en/photos/{photo}',
                    'title' => 'Photos',
                    'navTitle' => 'Photos',
                    'h1Title' => 'Photos',
                ],
            ],
            "de.photos.show" => [
                "method" => "GET",
                "uri" => "de/photos/{photo}",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@show',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'show',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos/{photo}',
                    'title' => 'Foto anzeigen',
                    'navTitle' => 'Foto',
                    'h1Title' => 'Foto anzeigen',
                ],
            ],
            "en.photos.show" => [
                "method" => "GET",
                "uri" => "en/photos/{photo}",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@show',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'show',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos/{photo}',
                    'title' => 'Show Photo',
                    'navTitle' => 'Photo',
                    'h1Title' => 'Show Photo',
                ],
            ]

        ]);
    }

    public function test_resource_using_only()
    {

        $this->routeTree->node('photos', function (RouteNode $node) {
            $node->resource('photo', '\RouteTreeTests\Feature\Controllers\TestController')->only([
                'index', 'create'
            ]);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.photos.index" => [
                "method" => "GET",
                "uri" => "de/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos',
                    'title' => 'Fotos',
                    'navTitle' => 'Fotos',
                    'h1Title' => 'Fotos',
                ],
            ],
            "en.photos.index" => [
                "method" => "GET",
                "uri" => "en/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos',
                    'title' => 'Photos',
                    'navTitle' => 'Photos',
                    'h1Title' => 'Photos',
                ],
            ],
            "de.photos.create" => [
                "method" => "GET",
                "uri" => "de/photos/erstellen",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos/erstellen',
                    'title' => 'Foto erstellen',
                    'navTitle' => 'Erstellen',
                    'h1Title' => 'Foto erstellen',
                ],
            ],
            "en.photos.create" => [
                "method" => "GET",
                "uri" => "en/photos/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos/create',
                    'title' => 'Create Photo',
                    'navTitle' => 'Resource',
                    'h1Title' => 'Create Photo',
                ],
            ],

        ]);
    }

    public function test_resource_using_except()
    {
        $this->routeTree->node('photos', function (RouteNode $node) {
            $node->resource('photo', '\RouteTreeTests\Feature\Controllers\TestController')->except([
                'show', 'update', 'destroy', 'edit', 'store'
            ]);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.photos.index" => [
                "method" => "GET",
                "uri" => "de/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos',
                    'title' => 'Fotos',
                    'navTitle' => 'Fotos',
                    'h1Title' => 'Fotos',
                ],
            ],
            "en.photos.index" => [
                "method" => "GET",
                "uri" => "en/photos",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos',
                    'title' => 'Photos',
                    'navTitle' => 'Photos',
                    'h1Title' => 'Photos',
                ],
            ],
            "de.photos.create" => [
                "method" => "GET",
                "uri" => "de/photos/erstellen",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/photos/erstellen',
                    'title' => 'Foto erstellen',
                    'navTitle' => 'Erstellen',
                    'h1Title' => 'Foto erstellen',
                ],
            ],
            "en.photos.create" => [
                "method" => "GET",
                "uri" => "en/photos/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'photos',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/photos/create',
                    'title' => 'Create Photo',
                    'navTitle' => 'Resource',
                    'h1Title' => 'Create Photo',
                ],
            ],

        ]);
    }


}