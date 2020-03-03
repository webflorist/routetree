# webflorist/routetree
**Advanded route management for Laravel 5.5 and later**

This package includes a special API for creating and accessing Laravel-routes and route-related information. It's main concept is to create a hierarchical multi-language RouteTree using an expressive syntax (mostly mimicking Laravel's own). Using that hierarchy, RouteTree can be used to easily create:
  * Any kind of navigation.
  * Language-agnostic links.
  * Language-switching menus.
  * Breadcrumb-menus.
  * Sitemap-menus.

Here is a complete feature overview:

* **Automatic path generation**  of routes for all configured languages with the locale as the first path-segment (e.g. `en/company/team/contact`).
* **Automatic route name generation** of expressive route names (e.g. `en.company.team.contact.get`).
* **Automatic inheritance** of various route-settings (mimicking Laravels `Route::group()`).
* **`Payload`** functionality to set any custom data for your routes (e.g. page title, meta description, `includeInMenu`) and retrieve it anywhere in your application.
* **Automatic translation** via various data-sources (e.g. using structured language-files within a folder-tree mirroring the hierarchical RouteTree structure). This is used for:
    * **Localized path-segments** (e.g. `en/company/team/contact` for english and `de/firma/team/kontakt` for german).
    * **Localized route keys for parameter- or resource-routes** using the corresponding Eloquent Models.
    * **Localized `Payload`** (page-titles and any other custom information) - also for parameter- or resource-routes using the corresponding Eloquent Models.
    * You can also utilize this structure for page-content using the included `trans_by_route()` helper.)
* **Unique language-agnostic route IDs** (e.g. `company.team.contact`) to be used for various purposes anywhere in your app. Examples using the `route_node()` helper function:
    * Link in current language:<br/>`route_node('company.team.contact')->getUrl()`.
    * Link in specific language:<br/>`route_node('company.team.contact')->getUrl()->locale('de')`.
    * Access hierarchical parents/siblings/children:<br/>`route_node('company.team')->getChildNodes()`        
    * Access the page title using:<br/>`route_node('company.team')->getTitle()`<br/>(falling back to upper cased route name - e.g. `Team`).
    * Access any other kind of custom information (via a `Payload`):<br/>`route_node('company.team')->payload->get('icon/description/keywords/author/layout/last_update/whatever')`.
* **Automatic locale setting**:
    * From the first segment of the current route name (e.g. `en.company.news.get`).
    * From a (automatically saved) session value.
    * From a `HTTP_ACCEPT_LANGUAGE` header sent by the client.
* **Automatic redirects**:
    * From the web root `/` to the language-specific home page (e.g. `/en`).
    * From paths with omitted locale (e.g. from `/company/team/contact` to `en/company/team/contact`).
* **XML-Sitemap generation** via an `artisan` command:
    * Automatic exclusion of `auth` routes and redirects.
    * Manual exclusion of routes and children.
    * Resolving of all possible route keys for parameter/resource routes.
    * Setting of optional tags (lastmod, changefreq, priority) via fluent setters, or by accessing an Eloquent Model (for parameter/resource routes).
* **Cacheable** (in combination with Laravel's route caching).
* **REST-API** to retrieve list of routes or various information or `Payload` from specific routes.

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Accessing the RouteTree-service](#accessing-the-routetree-service)
- [Defining the RouteTree](#defining-the-routetree)
- [Retrieving Nodes from the RouteTree](#retrieving-nodes-from-the-routetree)
- [Generating URLs](#generating-urls)
- [Route Payload](#route-payload)
- [Automatic Translation](#automatic-translation)
- [Caching](#caching)
- [Sitemap Generation](#sitemap-generation)
- [API](#api)
- [Important RouteTree-methods](#important-routetree-methods)
- [Important RouteNode-methods](#important-routenode-methods)
- [Helper functions](#helper-functions)

## Installation
1. Require the package via composer:  
`php composer require webflorist/routetree`
2. Publish config:  
`php artisan vendor:publish --provider="Webflorist\RouteTree\RouteTreeServiceProvider"`
2. Define all locales you want to use on your website under the key `locales` inside your `routetree.php` config file.  
E.g.: `'locales' => ['en','de']`.  
(Alternatively you can set it to `null` to enforce a single-language app (using config `app.locale`).)

Note that this package is configured for automatic discovery for Laravel. Thus the package's Service Provider `Webflorist\RouteTree\RouteTreeServiceProvider` as well as the `RouteTree` alias will be automatically registered with Laravel.

## Accessing the RouteTree-service
There are several ways to access the RouteTree service:
 * via helper function:  `route_tree()`
 * via Laravel facade: `\RouteTree::`
 * via Laravel container:  `app('Webflorist\RouteTree\RouteTree')` or `app()['Webflorist\RouteTree\RouteTree']`

The following code examples will use the helper-function `RouteTree::`.

## Defining the RouteTree

Just like with Laravel's own routing, your can define the RouteTree in your `routes/web.php`.

For better comparability of syntaxes, wherethe following examples will correspond to the ones presented in [Laravel's Routing documentation](https://laravel.com/docs/master/routing) where possible. They will also assume 2 configured languages (`'en','de'`) - if not otherwise stated.

### Basic Routing
```php
RouteTree::node('foo', function (RouteNode $node) {
    $node->get(function() {
        if (\App::getLocale() === 'de') {
            return 'Hallo Welt';
        }
        return 'Hello World';
    });
});
```

The `node()` method creates a RouteNode with name (and ID) `foo` and is then setup using the closure in it's second parameter.

A RouteNode itself is comparable to Laravel's Route Groups. It does not per se result in any registered routes, but centralizes and shares various data (e.g. path, middleware, namespace, etc.) with it's actions and inherits them to any child nodes.

The `$node->get()` call adds a RouteAction named `get` using the HTTP request method `GET` and a closure as it's callback.

A RouteAction results in one generated Laravel Route per configured language.

The above code will register the following routes:
- Route with name `de.foo.get` and path `de/foo`
- Route with name `en.foo.get` and path `en/foo`

 As with Laravel's syntax you can also state the action's callback using `Controller@method`:

```php
RouteTree::node('foo')->get('Controller@method');
```

In the above example, the RouteNode's setup closure is skipped and instead the `get` call is directly chained to the `node` call. This is an alternative way to setup (which returns a RouteNode and thus allows chaining of various fluent methods), resulting in a more readable one-liner. Once a RouteNode becomes a little more complex and has several child-nodes, using a setup-closure is recommended instead. Also be wary, that action-creating methods (such as `get`, `post`, `redirect`, `view`, etc.) return the RouteAction object instead of the RouteNode.

Both syntax variants will be used in the following examples.

### Available RouteActions
RouteNodes provide public methods to register RouteActions that respond to any HTTP verb:

```php
RouteTree::node('foo', function (RouteNode $node) {
    $node->get($callback);
    $node->post($callback);
    $node->put($callback);
    $node->patch($callback);
    $node->delete($callback);
    $node->options($callback);
});
```

### Redirect Actions
You can also define redirecting nodes and state the target nodes by their ID:
```php
RouteTree::node('here')->redirect('there');

RouteTree::node('there', function (RouteNode $node) {
    $node->get(function() {
        return 'You are now there';
    });
});
```

By default, `$node->redirect()` returns a 302 status code.

You can customize the status code using the optional second parameter:  
`$node->redirect('there', 301);`.

You can also use `$node->permanentRedirect()` to return a 301 status code.

### View Actions
If a RouteNode should only return a view, you can use the `view` method:
```php
RouteTree::node('welcome')->view('welcome');
```
You can pass data to the view via the second parameter of the `view` method.

### Configuring the Root Node
The `RouteTree::node()` method used in the above examples automatically creates nodes with the root node as parent. You can configure the root node itself using the `root` method instead:
```php
RouteTree::root()->view('welcome');
```
You cannot state a name for the root node. It's name and ID will always be an empty string (`''`).

### Adding Child Nodes
There are several ways to create a node as a child of another node:
- by calling `$node->child($childName, $childCallback)` within the parent's setup-callback.
- by stating the parent RouteNode's ID as the third parameter of the `node` method: `RouteTree::node($childName, $childCallback, $parentId);`
- by using `RouteTree::getRoute('parent')->child('child', $childCallback)`

The first variant will be used in any further examples. It builds the RouteTree using nested closures, which has the benefit of representing the hierarchical RouteTree within the defining code indentation.

Child nodes will automatically receive a unique node ID representing the hierarchy of it's ancestors. For example a child with name `bar` of a parent called `foo` will have the ID `foo.bar`.

The same happens with path segments, resulting in for example `en/foo/bar`.

You can disable inheriting the segment to it's descendants by calling `$node->inheritSegment(false)`. This is useful, if you want to use a RouteNode simply for grouping purposes without a representation in the URL-path.

As with Laravel's Route groups, middleware and (controller-)namespaces will be also inherited by default.

### Path Segments

By default a RouteNode's name will also be it's path segment. But you can also state a different segment for a node by calling the RouteNode's `segment` method:
```php
RouteTree::node('company', function (RouteNode $node) {    
    $node->segment('our-great-company');
    $node->get($callback);
});
```

The above code will register the following routes:
- Route with name `de.company.get` and path `de/our-great-company`
- Route with name `en.company.get` and path `en/our-great-company`

You can define localized path segments by handing a `LanguageMapping` object including segments for all languages:
```php
$node->segment(
    Webflorist\RouteTree\LanguageMapping::create()
        ->set('en', 'our-great-company')
        ->set('de', 'unsere-tolle-firma')
);
```

This will register the following routes:
- Route with name `de.company.get` and path `de/our-great-company`
- Route with name `en.company.get` and path `en/unsere-tolle-firma`

You can also handle segment-translations via your language-files using the [Automatic Translation](#automatic-translation) functionality.

### Middleware

You can assign middleware to RouteNodes using:
```php
$node->middleware('auth');
```

This will attach the `auth` middleware to all of the RouteNode's actions and inherit it to all descendant nodes.

Middleware-parameters can be stated in the second parameter of the `middleware` method.

Inheritance of middleware can be disabled by handing boolean `false` as the third parameter of the `middleware` method.

In case you want a descendant node to NOT use an inherited middleware, simply state the following in the descendant's callback:
```php
$node->skipMiddleware('auth');
```

There might also be situations, where you want a specific action of a RouteNode to have additional middleware or skip a middleware defined on the RouteNode. You can achieve this by chaining the `middleware` call to the action-call. Here is an example:
```php
RouteTree::node('user', function (RouteNode $node) {
    $node->middleware('auth');
    $node->get($callback)->skipMiddleware('auth');
    $node->post($callback);
    $node->delete($callback)->middlware('admin');
});
```

This will register the following routes:
- `GET` Routes with no middleware.
- `POST` Routes with the `auth` middleware.
- `DELETE` Routes with both the `auth` and `admin` middleware.

### Controller Namespaces

By default all `Controller@method` callback definitions will use `App\Http\Controllers` as the namespace.

Using a RouteNode's `namespace` method will append a segment to that namespace and inherit it to it's descendants. Inheritance can be overruled by simply prefixing the namespace with a backslash.
```php
RouteTree::node('account', function (RouteNode $node) {
    $node->namespace('Account');
    $node->child('address' function (RouteNode $node) {
        $node->get('AddressController@get');
        // will point to `App\Http\Controllers\Account\AddressController`
    })
    $node->child('password' function (RouteNode $node) {
        $node->get('\My\Other\Namespace\PasswordController@get');
        // will point to `My\Other\Namespace\PasswordController`
    })
});
```

### Route Parameters
The following code will result in the creation of the routes `en/user/{id}` and `de/user/{id}`.
```php
RouteTree::node('user', function (RouteNode $node) {
    $node->child('id', function (RouteNode $node) {        
        $node->parameter('id');
        $node->get('id', function ($id) {
            return 'User '.$id;
        });
    });
});
```

You can also set regular expression constraints for parameters:
```php
$node->parameter('id')->regex('[0-9]+');
```

When using `parameter` or `resource` nodes, you might also want to be able to translate a route key (e.g. to realize a language-switching menu for a blog-article, which has different slugs for different languages.)

There are two ways to achieve this:

- You can state a static list of possible route keys of a parameter for each language using this syntax (enabling `translation` through the array-keys (0, 1, 'whatever')):  
```php
$node->parameter('blog_category')->routeKeys(LanguageMapping::create()
    ->set('en', [
        0 => 'search-engine-optimization',
        1 => 'web-development'
    ])
    ->set('de', [
        0 => 'suchmaschinen-optimierung',
        1 => 'web-entwicklung'
    ])
);
```
- You can also translate the route key via an `Eloquent` model. There are two requirements for this:
1. An `Eloquent` model must be stated using the `model` method of a `RouteParameter` or `RouteResource`:  
`$node->resource('blog_category', 'BlogCategoryController')->model('App\BlogCategory');` or  
`$node->parameter('blog_category')->model('App\BlogCategory');`
2. The model must implement the interface `Webflorist\RouteTree\Interfaces\TranslatesRouteKey` and subsequently the `translateRouteKey` method. Here is an example implementation:
```php
public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string
{
    return BlogCategory::bySlug($value, $fromLocale)->slugs->where('locale', $toLocale)->first()->slug ?? $value;
}
```

### Resourceful RouteNodes

Akin to Laravel's [`Route::resource()` method](https://laravel.com/docs/master/controllers#restful-resource-controllers), RouteTree can also register resourceful routes:
```php 
RouteTree::node('photos')->resource('photo', 'PhotoController');
```

This will generate a full set of resourceful routes in all languages:

HTTP-Verb   | Route Name          | URI                      | Action / Controller Method
------------|--------------------|---------------------------|--------------
GET         | `en.photos.index`   | `/en/photos`              | index
GET         | `en.photos.create`  | `/en/photos/create`       | create
POST        | `en.photos.store`   | `/en/photos`              | store
GET         | `en.photos.show`    | `/en/photos/{photo}`      | show
GET         | `en.photos.edit`    | `/en/photos/{photo}/edit` | edit
PUT/PATCH	| `en.photos.update`  | `/en/photos/{photo}`      | update
DELETE      | `en.photos.destroy` | `/en/photos/{photo}`      | destroy
GET         | `de.photos.index`   | `/de/photos`              | index
GET         | `de.photos.create`  | `/de/photos/create`       | create
POST        | `de.photos.store`   | `/de/photos`              | store
GET         | `de.photos.show`    | `/de/photos/{photo}`      | show
GET         | `de.photos.edit`    | `/de/photos/{photo}/edit` | edit
PUT/PATCH   | `de.photos.update`  | `/de/photos/{photo}`      | update
DELETE      | `de.photos.destroy` | `/de/photos/{photo}`      | destroy
 
Partial resource routes are also supported using the `only` or `except` methods:
```php
$node->resource('photo', 'PhotoController')->only(['index', 'show']);

$node->resource('photo', 'PhotoController')->except(['create', 'store', 'update', 'destroy']);
```

Resource nodes can also have child-nodes. In this case call the `child` method on `$node->resource` instead of `$node`:
```php 
RouteTree::node('photos', function (RouteNode $node) {
    $node->resource('photo', 'PhotoController')
    $node->resource->child('featured', function (RouteNode $node) {
        $node->get('PhotoController@featured');
    });
});
```

The above code will additionally generate the following routes:
- Route `en.photos.featured.get` with the URI `en/photos/{photo}/featured`.
- Route `de.photos.featured.get` with the URI `de/photos/{photo}/featured`.

## Retrieving Nodes from the RouteTree

Now that we have defined the RouteTree, it's RouteNodes can be accessed anywhere in your application using the `route_node()` helper:
- `route_node()`  
is a shortcut for `RouteTree::getCurrentNode()` and will return the currently active RouteNode.
- `route_node('company.team.contact')`  
is a shortcut for `RouteTree::getNode('company.team.contact')` and will return the RouteNode with ID `company.team.contact`

If RouteTree fails to find the current/specified node, it will throw a `NodeNotFoundException`, except a fallback node is set in the config `routetree.fallback_node`. The default config sets the fallback node to the root node, since you will probably want to inhibit `NodeNotFoundExceptions` in a production environment.

## Generating URLs

One of RouteTree's central use cases is to create language-agnostic links. Both RouteNodes and RouteActions have a `getUrl()` method, that returns a `RouteUrlBuilder` object, which will generate the corresponding URL when cast to a string.

`(string) route_node('company.team.contact')->getUrl()` will return the URL of the RouteNode's action. If a node has multiple actions, it will return the link to it's first `get` action (or `index` actions with `resources`).

The returned `RouteUrlBuilder` object has several fluent setters to modify the generated link:

* ->**locale** ( ?string $locale=null ) : RouteUrlBuilder  
`(string) route_node('company')->getUrl()->locale('en')` will return the URL in english language (e.g. `en/company`). (defaults to current locale)

* ->**absolute** ( ?bool $absolute=null ) : RouteUrlBuilder  
`(string) route_node('company')->getUrl()->absolute(false)` will return a relative path instead of an absolute URL inkl. the domain (default can be configured in `routetree.absolute_urls`)

* ->**action** ( string $locale ) : RouteUrlBuilder  
`(string) route_node('photos')->action('create')` will return the URL of the `resource` action `create` (effectively appending `'/create'` to the URL by default in `en` locale). See the table at [Resourceful RouteNodes](#resourceful-routenodes) for details. By default the `index` or the first `GET` action will be used. Note that with the actions `show`, `edit`, `update` and `destroy` you will also have to state the route keys to set for the url `parameter(s)` (see just below).  

* ->**parameters** ( array $parameters ) : RouteUrlBuilder  
`(string) route_node('photos')->action('edit')->parameters(['photo' => 'my-slug'])` would result in the URL `/en/photos/my-slug/edit` using the locale `en`. Any URL to a Route containing one or more parameters will need values to fill in for those parameters, and thus a key within the handed array. E.g. `photo/{photo_id}/comments/{comment_id}` would require ``['photo_id' => $photoId, 'comment_id' => $commentId]`` to be passed. Any missing route keys (aka parameter-values aka slugs) are taken from the currently active Laravel `Request` - if possible.

## Route Payload

You can define an access any information you want to a RouteNode using it's associated `RoutePayload` object, which is publicly accessible via a node's `payload` property.

### Defining Payload

You can set a payload item directly in the `RoutePayload` object using the following syntax-options:
- by calling a `RoutePayload`'s `set` method:  
`$node->payload->set('title', 'My photos');`
- by calling a magic setter named like the key you'd like to set:  
`$node->payload->title('My photos');`
- by simple property definition:  
`$node->payload->title = 'My photos';`

The value of a payload can be any data type as well as a `Closure`. The Closure will receive two parameters:
- Array of `route parameter => route key` pairs, to retrieve the payload for (this way you can have payload depend on the current route parameters).
- Locale of the language to retrieve the payload for.

As with path segments, any payload-item can also be multilingual using a `LanguageMapping` object:
```php
$node->payload->title = LanguageMapping::create()
        ->set('en', 'My photos')
        ->set('de', 'Meine Photos')
);
```

You can also handle payload translations via your language-files using the [Automatic Translation](#automatic-translation) functionality.

If you want to have different values of a payload depending on an action, you can override a RouteNode's payload using a RouteAction's payload. Here is an example:
```php
$node->getAction('edit')->payload->set('title', 'Edit photo');
```

For `parameter/resource` nodes there is also the possibility to fetch payload from an `Eloquent` model. There are two requirements for this:
1. An `Eloquent` model must be stated using the `model` method of a `RouteParameter` or `RouteResource`:  
`$node->resource('photos', 'PhotoController')->model('App\Photo');` or  
`$node->parameter('photo')->model('App\Photo');`
2. The model must implement the interface `Webflorist\RouteTree\Interfaces\ProvidesRoutePayload` and subsequently the `getRoutePayload` method. Here is an example implementation:
```php
public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action)
{
    if ($payloadKey === 'title' && $action === 'show')
    {
        return self::find($parameters['photo'])->title;
    }
}
```

### Retrieving Payload

Payload can be retrieved anywhere in your app.  using the `get` method of a `RoutePayload`. Example using the current RouteNode and RouteAction:

```php
route_node()->payload->get('title');
```

This will look for the `title` payload using the following order:
1. Payload set directly in this class.
2. Payload set in the RouteNode's RoutePayload (only if this RoutePayload is RouteAction-specific.)
3. Payload returned from an Eloquent Model (only if RouteNode has a RouteParameter associated with an Eloquent Model, that implements ProvidesRoutePayload)
4. Using Auto-Translation by searching for payload at a translation-key relative to the RouteNode's ID (see [Automatic Translation](#automatic-translation)).

There are multiple use-cases, where this payload functionality can be useful
This is very useful to e.g.:

* Set the class of an icon, that should be visible in the menu next to the page-title.
* Set a language-specific abstract for each page, displayed on a sitemap.
* and much much more...

### Special `title` and `navTitle` payloads

Page titles (for meta tags, canonical tags, title attributes of links, navigation menus, breadcrumbs, h1-tags, etc.) are probably one of the most used applications for payloads. Also quite often you might want to have a special (shorter) title for pages in navigation menus. To simplify handing of this, RouteNodes and RouteActions have special `getTitle()` and `getNavTitle()` methods, that add some additional fallback magic:
- Not set `navTitle` falls back to `title`.
- Last fallback is always the upper-cased name of the RouteNode (e.g. `Photos`)
- Various actions of resource nodes already come with meaningful default page titles (e.g. `'Create Resource'` for `create` actions).

To utilize this magic, always use e.g. `route_node()->getTitle()` instead of `route_node()->payload->get('title')` and `route_node()->getNavTitle()` instead of `route_node()->payload->get('navTitle')`.

## Automatic Translation

RouteTree also includes some magic regarding automatic translation. The basic concept is to map the hierarchy of the RouteTree to a folder-structure within the localization-folder.

The config-key `localization.base_folder` sets the base-folder for the localization-files- and folders utilized by RouteTree. The default value is `pages`, which translates to the folder `\resources\lang\%locale%\pages`.

There are 2 seperate auto-translation-functionalities:
1. Auto-translation of a node's path-segment and payload (e.g. title, navTitle, description, etc.)
2. Auto-translation of regular page-content.

### Auto-translation of a node's path-segment and payload

This provides an easy and intuitive way of configuring multi-language variants of the path-segment, page-title, or any other custom information for routes through Laravel's localization-files.
 
Each RouteNode is represented as a folder, and within the folder for a node resides a file, that contains all auto-translation-information. How this file is named is configured under the config-key `localization.file_name`. The default value is `pages`, which means information on all 1st-level-pages should be placed in this file: `\resources\lang\%locale%\pages\pages.php`

**Example:**
Let's assume, you have defined the following RouteNodes (any actions or other options are missing for simplicity's sake):
```php
    RouteTree::node('company', function (RouteNode $node) {
        $node->child('history', ...);
        $node->child('team', function (RouteNode $node) {
            $node->child('office', ...);
            $node->child('service', ...);
        });
    });
    RouteTree::node('contact', ...);
```

Please note, that no path-segment, page-titles or custom information is defined on any node. We will use auto-translation for this.

To use auto-translation, the following file- and folder-structure should be present within the defined base-folder for each locale (per default `\resources\lang\%locale%\pages`):
``` 
 .
 ├── pages.php
 ├── company
     ├── pages.php
     └── team
         └── pages.php

```

Each pages.php-file includes auto-translation information for the child-nodes of the node, which corresponds to the folder it resides in. Let's see a german-language example of the contents of these files:

./pages.php:
```php
<?php
return [
    'segment' => [
        'company' => 'firma',
        'contact' => 'kontakt',
    ],
    'title' => [
        'company' => 'Über unsere Firma',
        'contact' => 'Kontaktieren Sie uns!',
        '' => 'Startseite',
    ],
    'abstract' => [        
        'company' => 'Hier finden Sie allgemeine Informationen über unsere Firma.',
        'contact' => 'Hier finden Sie Möglichkeiten, mit uns in Kontakt zu treten.',
    ]
];
```

Note that there is an additional entry in the title-array with an empty string as the key and "Home" as the value. This is title of the root-node (as the root-node's ID and name is always an empty string `''`).

./company/pages.php:
```php
<?php
return [
    'segment' => [
        'history' => 'geschichte',
        'team' => 'mitarbeiter',
    ],
    'title' => [
        'history' => 'Die Firmengeschichte',
        'team' => 'Unsere Mitarbeiter',
    ],
    'description' => [
        'history' => 'Hier finden Sie die Entstehungsgeschichte unserer Firma.',
        'team' => 'Hier sind unsere Mitarbeiter zu finden.',
    ]
];
```

./company/team/pages.php:
```php
<?php
return [
    'segment' => [
        'office' => 'buero',
        'service' => 'kundendienst',
    ],
    'title' => [
        'office' => 'Büro',
        'service' => 'Kundendienst',
    ],
    'description' => [
        'office' => 'Hier finden Sie unsere Büro-Mitarbeiter.',
        'service' => 'Hier finden Sie unsere Service-Mitarbeiter.',
    ]
];
```

With this setup, the segments defined in the language-files will automatically be used for the route-paths of their corresponding nodes.

Also the title will be fetched with each getTitle-call submitted for a specific node (e.g. `route_node('company.team.service')->getTitle()` would return `Büro`, if the current locale is `de`.

The same thing is possible with the description (or any other payload). (e.g. `route_node('company.team.service')->payload->get('description')` would return `Hier finden Sie unsere Service-Mitarbeiter.`, if the current locale is `de`.

You can also set action-specific titles or navTitles via auto-translation by appending an underscore and the action to the node-name. This is very useful for resource nodes. Here is an example:

```php
<?php
return [
    'title' => [
        'users' => 'Users',
        'users_create' => 'Create new user',
        'users_show' => 'User :userName',
        'users_edit' => 'Edit user :userName',
    ],
];
```

### Auto-translation of regular page-content

With most websites you will want to translate page-content in your views. RouteTree includes a handy helper-function called `trans_by_route()`, that will use the same folder-structure but with the language-file named as the last RouteNode.

Using the example above the location of this file for the `office` page would be : `./company/team/office.php`

## Caching
If you are using Laravels route caching, RouteTree must cache it's own data too. So instead of `'artisan route:cache'` use RouteTree's caching-command, which will also take care of caching Laravel's routes:
```
php artisan routetree:route-cache
```

## Sitemap Generation
Having an up-to-date `sitemap.xml` file is an important criteria for a modern search engine optimized website. RouteTree includes an artisan command, that will create such a file:
```
php artisan routetree:generate-sitemap
```

Per default the output-file will be at `'public/sitemap.xml'`. You can however configure this in RouteTree's config file.

Any URL's in the sitemap will use `config('app.url')` as the base url automatically. But you can also state a different value unfer the `routetree.sitemap.base_url` config.

Per default all routes created with RouteTree will be present in the sitemap. There are some exclusion criteria though:
- Only `GET` routes will be included.
- Routes using the a middleware configured under `routetree.sitemap.excluded_middleware` will be automatically excluded (defaults to `['auth']`).
- Redirect routes will be automatically excluded.
- Routes with `parameters` can only be included, if RouteTree can retrieve all possible values for these parameters. There are two ways to achieve this:
    - using the `routeKeys()` method (see [Route Parameters](#route-parameters))
    - by stating an `Eloquent` model implementing the interface `Webflorist\RouteTree\Interfaces\ProvidesRouteKeyList` and thus the method `getRouteKeyList()`. A (single-language) default implementation is included in the trait `Webflorist\RouteTree\Interfaces\Traits\ProvidesRouteKeyListDefault`:

```php
    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): array
    {
        return self::pluck(
            (new self())->getRouteKeyName()
        )->toArray();
    }
```

Furthermore you can also explicitly exclude a RouteNode (and all it's children) from the sitemap:
```php
$node->sitemap->exclude();
```

A `sitemap.xml` also allows the definition of additional information for search engines (see https://www.sitemaps.org/protocol.html#xmlTagDefinitions). You can state this data for a node using the following code:
```php
$node->sitemap
    ->lastmod(Carbon::parse('2019-11-16T17:46:30.45+01:00'))
    ->changefreq('monthly')
    ->priority(1.0);
```
Furthermore you can also use payload translation (either via an `Eloquent` model or via language files) to automatically retrieve these values.

## API

RouteTree also includes an API, that allows fetching information about routes registered with RouteTree. The API must be enabled via config `routetree.api.enabled` and has the default base-url `api/routetree/` (also configurable).

At the moment there are 2 endpoints:
- `GET api/routetree/routes`:  
Returns collection of routes registered with Routetree.
- `GET api/routetree/routes/{route_name}`:  
Returns information about a route registered with Routetree.

## Events

RouteTree dispatches events in various cases:

- `\Webflorist\RouteTree\Events\LocaleChanged`  
Is dispatched, when the locale saved in session by the `RouteTreeMiddleware` is changed. The old locale is available via the `$oldLocale` property of the event, and the new locale via `$newLocale`.

- `\Webflorist\RouteTree\Events\NodeNotFound`  
Is dispatched, when `route_node()` is called and the current or specified node could not be found. The specified RouteNode ID is available via the `$nodeId` property of the event, and is `null` in case no current node was found.

- `\Webflorist\RouteTree\Events\Redirected`  
Is dispatched, when the `RouteTreeMiddleware` performs an automatic redirect. The destination URI is available via the `$toUri` property of the event, and the source URI via `$fromUri`.

## Important RouteTree-methods

For the already mentioned and explained methods `root()`, `node()` please see the corresponding sections above.

Here are some other useful methods of the RouteTree-class:

* **getRootNode**: Get the root-node which is also the whole RouteTree.
* **getCurrentNode**: Get the currently active RouteNode. (use `route_node()` as shortcut)
* **getCurrentAction**: Get the currently active action.
* **doesNodeExist**: Checks, if a node within the RouteTree. It takes one parameter, which is the node-id to be checked. (e.g.: `RouteTree::doesNodeExist('company.team.office')`)
* **getNode**: Get's and returns the RouteNode via it's Id. (e.g.: `RouteTree::getNode('company.team.office')`; use `route_node('company.team.office')` as shortcut)

### Important RouteNode-methods

For the already mentioned and explained methods `getUrl`, `getTitle` and `getNavTitle` please see the corresponding sections above.

Here are some other useful methods of the RouteNode-class:

* **getParentNode**: Gets the parent node of this node. (e.g.: `route_node('company.team.office')->getParentNode()` would retrieve the node with the ID `company.team`.)
* **getParentNodes**: Gets an array of all hierarchical parent-nodes of this node (with the root-node as the first element). (e.g.: `route_node()->getParentNodes()` would retrieve an array of all ancestral-nodes of the currently active node up to to the root-node. This is very useful for site-maps or breadcrumbs.)
* **hasChildNodes**: Checks, if this node has any child-nodes.
* **getChildNodes**: Get an array of all child-nodes (e.g. useful for sub-menus).
* **hasChildNodes**: Checks, if this node has any child-nodes.
* **getChildNode**: Checks, if this node has a child-node with the stated name.
* **getId**: Get the full Id of this node.
* **isActive**: Checks, if the current node is currently active (optionally with the desired parameters) (e.g. useful to apply a CSS-class to an active link).
* **nodeOrChildIsActive**: Checks, if the current node or one of it's children is currently active (optionally with the desired parameters) (e.g. useful to apply a CSS-class to an active link).

## Helper functions

Several helper-functions are included with this package:

* **route_tree**: Gets the RouteTree singleton from Laravel's service-container. It can be used anywhere in your application (controllers, views, etc.) to access the RouteTree service.

* **route_node**:
    - If called with no parameter: get the currently active RouteNode.
    - If called with a parameter: `route_node('company.team.contact')` will return the RouteNode with ID `'company.team.contact'`.
    
* **route_node_url**: Shortcut for `route_node()->getUrl()`.

* **trans_by_route**: Translates page-content using the current node's content-language-file (see section `Auto-translation of regular page-content` above).