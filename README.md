# webflorist/routetree
**Advanded route management for Laravel 5.5 and later**

This package includes a special API for creating and accessing Laravel-routes and route-related information. It's main concept is to create a hierarchical multi-language RouteTree using an expressive syntax (mostly mimicing Laravel's own). Using that hierarchy, RouteTree can be used to easily create:
  * Any kind of navigation.
  * Language-agnostic links.
  * Language-switching menus.
  * Breadcrumb-menus.
  * Sitemap-menus.

Here is a complete feature overview:

* **Automatic path generation** of routes for all configured languages with the locale as the first path-segment (e.g. `en/company/team/contact`).
* **Automatic route name generation** of expressive route names (e.g. `en.company.team.contact.get`).
* **Automatic inheritance** of various route-settings (mimicing Laravels `Route::group()`).
* **Automatic translation** via various data-sources (e.g. using structured language-files within a folder-tree mirroring the hierarchical RouteTree structure). This is used for:
    * **Localized path-segments** (e.g. `en/company/team/contact` for english and `de/firma/team/kontakt` for german).
    * **Localized route keys for parameter- or resource-routes** using the corresponding Eloquent Models.
    * **Localized `payload`** (page-titles and any other custom information) - also for parameter- or resource-routes using the corresponding Eloquent Models.
    * You can also utilize this structure for page-content using the included `trans_by_route()` helper.)
* **`Payload`** functionality to set any custom data for your routes (e.g. page title, meta description, `includeInMenu`) and retrieve it anywhere in your application.
* **Unique language-agnostic route IDs** (e.g. `company.team.contact`) to be used for various purposes anywhere in your app. Examples using the `route_node()` helper function:
    * Link in current language: `route_node('company.team.contact')->getUrl()`.
    * Link in specific language: `route_node('company.team.contact')->getUrl()->locale('de')`.
    * Access hierarchical parents/siblings/children: e.g. `route_node('company.team')->getChildNodes()`        
    * Access the page title using: `route_node('company.team')->getTitle()` (falling back to upper cased route name - e.g. `Team`).
    * Access any other kind of custom information (via a `payload`): `route_node('company.team')->payload->get('icon/description/keywords/author/layout/last_update/whatever')`.
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
* **REST-API** to retrieve list of routes.

## Installation
1. Require the package via composer: `php composer require webflorist/routetree`
2. Publish config: `php artisan vendor:publish --provider="Webflorist\RouteTree\RouteTreeServiceProvider"`
2. Define all locales you want to use on your website under the key `locales` inside your `routetree.php` config file. E.g.: `'locales' => ['en','de']`. (Alternatively you can set it to `null` to enforce a single-language app (using config `app.locale`).)

Note that this package is configured for automatic discovery for Laravel. Thus the package's Service Provider `Webflorist\RouteTree\RouteTreeServiceProvider` as well as the `RouteTree` alias will be automatically registered with Laravel.

## Usage

### Accessing the RouteTree-service
There are several ways to access the RouteTree service:
 * via helper function: `route_tree()`
 * via Laravel facade: `\RouteTree`
 * via Laravel container:  `app('Webflorist\RouteTree\RouteTree')` or `app()['Webflorist\RouteTree\RouteTree']`

The following code examples will use the helper-function `route_tree()`.

### Defining the RouteTree

Just like with Laravel's own routing, your can define the RouteTree in your `routes/web.php`. For better comparability of syntaxes, the following examples will correspond to the ones presented in Laravel's documentation (https://laravel.com/docs/master/routing). They will also assume 2 configured languages (`'en','de'`) - if not otherwise stated.

#### Basic Routing
```php
route_tree()->node('foo', function (RouteNode $node) {
    $node->get(function() {
        return \App::getLocale() === 'de' ? 'Hallo Welt' : 'Hello World';
    });
});
```

The `node()` method creates a RouteNode with name/id `foo`. A RouteNode itself is comparable to Laravel's Route Groups. It does not per se result in any registered routes, but centralizes and shares various data (e.g. path, middleware, namespace, etc.) with it's actions and inherits them to any child-nodes.

The `$node->get()` call creates a RouteAction named `get` using a closure. A RouteAction results in one generated Laravel Route per configured language. As with Laravel's syntax you can also state the action's callback using `Controller@method`.

The above code will register the following routes:
- Route with name `de.foo.get` and path `de/foo`
- Route with name `en.foo.get` and path `en/foo`

#### Available Node Actions
RouteNodes provide public methods to register routes that respond to any HTTP verb:

```php
route_tree()->node('foo', function (RouteNode $node) {
    $node->get($callback);
    $node->post($callback);
    $node->put($callback);
    $node->patch($callback);
    $node->delete($callback);
    $node->options($callback);
});
```

#### Redirect Actions
You can also define redirecting nodes and state the target nodes by their ID:
```php
route_tree()->node('here', function (RouteNode $node) {
    $node->redirect('there');
});
route_tree()->node('there', function (RouteNode $node) {
    $node->get(function() {
        return 'You are now there';
    });
});
```

By default, `$node->redirect()` returns a 302 status code.

You can customize the status code using the optional second parameter: `$node->redirect('there', 301);`.

You can also use `$node->permanentRedirect()` to return a 301 status code.

#### View Actions
If a RouteNode should only return a view, you can use the `view` method:
```php
route_tree()->node('welcome', function (RouteNode $node) {
    $node->view('welcome');
});
```
You can pass data to the view via the second parameter of the `view` method.

#### Configuring the Root Node
The `route_tree()->node()` method used in the above examples automatically creates nodes with the root node as parent. You can configure the root node itself using the `root` method instead:
```php
route_tree()->root(function (RouteNode $node) {
    $node->view('welcome');
});
```
You cannot state a name for the root node. It's name and ID will always be an empty string (`''`).

#### Adding Child Nodes
There are several ways to create a node as a child of another node:
- by calling `$node->child($childName, $childCallback)` within the parent's callback.
- by stating the parent RouteNode's ID as the third parameter of the `node` method: `route_tree()->node($childName, $childCallback, $parentId);`
- using `route_tree()->getRoute('parent')->child('child', $childCallback)`

The first variant will be used in any further examples. It builds the RouteTree using nested closures, which has the benefit of representing the hierarchical RouteTree within the defining code indentation.

Child nodes will automatically receive a unique node ID representing the hierarchy of it's ancestors. For example a child with name `bar` of a parent called `foo` will have the ID `foo.bar`.

The same happens with path segments, resulting in for example `en/foo/bar`. You can disable inheriting the segment to it's descendants by calling `$node->inheritSegment(false)`. This is useful, if you want to use a RouteNode simply for grouping purposes without a representation in the URL-path.

As with Laravel's Route groups, middleware and (controller-)namespaces will be also inherited by default.

#### Path Segments

By default a RouteNode's name will also be it's path segment. But you can also state a different segment for a node by calling the RouteNode's `segment` method:
```php
route_tree()->node('company', function (RouteNode $node) {    
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
        ->set('en', 'company')
        ->set('de', 'firma')
);
```

This will register the following routes:
- Route with name `de.company.get` and path `de/company`
- Route with name `en.company.get` and path `en/firma`

You can also handle segment-translations via your language-files using the `Automatic translation` functionality (see below).

#### Middleware

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
route_tree()->node('user', function (RouteNode $node) {
    $node->middleware('auth');
    $node->get($callback)->skipMiddleware('auth');
    $node->post($callback);
    $node->delete($callback)->middlware('admin');
});
```

This will register the following routes:
- GET Routes with no middleware.
- POST Routes with the `auth` middleware.
- DELETE Routes with both the `auth` and `admin` middleware.

#### Controller Namespaces

By default all `Controller@method` callback definitions will use `App\Http\Controllers` as the namespace.

Using a RouteNode's `namespace` method will append a segment to that namespace and inherit it to it's descendants. Inheritance can be overruled by simply prefixing the namespace with a backslash.
```php
route_tree()->node('account', function (RouteNode $node) {
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

#### Route Parameters
The following code will result in the creation of the routes `en/user/{id}` and `de/user/{id}`.
```php
route_tree()->node('user', function (RouteNode $node) {
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

#### Resourceful Route Nodes

Akin to Laravel's [`Route::resource()` method](https://laravel.com/docs/master/controllers#restful-resource-controllers), RouteTree can also register resourceful routes:
```php 
route_tree()->node('photos', function (RouteNode $node) {
    $node->resource('photo', 'PhotoController');
});
```

This will generate a full set of resourceful routes:

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
 
Partial resource routes are also supported:
```php
   $node->resource('photo', 'PhotoController')->only(['index', 'show']);
   $node->resource('photo', 'PhotoController')->except(['create', 'store', 'update', 'destroy']);
```

Resource nodes can also have child-nodes. In this case call the `child` method on `$node->resource`:
```php 
route_tree()->node('photos', function (RouteNode $node) {
    $node->resource('photo', 'PhotoController')
    $node->resource->child('featured', function (RouteNode $node) {
        $node->get('PhotoController@featured');
    });
});
```

The above code will additionally generate the following routes:
- Route `en.photos.featured.get` with the URI `en/photos/{photo}/featured`.
- Route `de.photos.featured.get` with the URI `de/photos/{photo}/featured`.

### Retrieving Nodes from the RouteTree

Now that we have defined the RouteTree, it's RouteNodes can be accessed anywhere in your application using the `route_node()` helper:
- `route_node()` will return the currently active RouteNode.
- `route_node('company.team.contact')` will return the RouteNode with ID `'company.team.contact'`

### Generating URLs

...............

### Route Payload

You can add any information you want to a RouteNode using it's associated `RoutePayload` object, which is publicly accessible via a node's `payload` property. You can set a payload item using the following syntax-options:
- by calling a `RoutePayload`'s `set` method: `$node->payload->set('title', 'Title of my node')`
- by calling a magic setter: `$node->payload->title('Title of my node')`
- by simple property definition: `$node->payload->title = 'Title of my node'`

The value of a payload can be any data type as well as a closure.

As with path segments, any payload-item can also be multilingual using a `LanguageMapping` object:
```php
$node->payload->title = LanguageMapping::create()
        ->set('en', 'Title of my node')
        ->set('de', 'Titel meines Knotens')
);
```

You can also handle payload translations via your language-files using the `Automatic translation` functionality (see below).


Payload can be retrieved anywhere in your app using one of the following options (by calling the `get()`-method of a RouteNode-object, or by calling a corresponding magic-getter-method (e.g. `getData('abstract')` and `getAbstract()` would both retrieve any information set in the route-generation-array under the key `abstract`.

Furthermore custom data is handled the same way as the `title` above. This means you can set this information statically for all languages or per language using a language-array, or even set a closure. Just look at the examples above for setting a `title` and use any desired keyword instead. And as with the `title` usage, you can also handle custom parameters using auto-translation (as described below). 
 
This is very useful to e.g.:

* Set the class of an icon, that should be visible in the menu next to the page-title.
* Set a language-specific abstract for each page, displayed on a sitemap.
* and much much more...


###### **'title'**:

This option is to set a page-title for a node you can then use in various locations in your application (e.g. menus, sitemaps, breadcrumbs, etc.).

This title can then be retrieved by calling the `getTitle()`-method of a RouteNode-object (e.g. `route_tree()->getNode('contact')->getTitle()`).

The title can be set in various ways. You can set it as an option in the routes-array under the key `title`, with it's value either being a string, that is then used for all languages or as an associative array setting a separate title per language using, or you can also resolve the title by defining a closure.
 
**Example 1: Setting a static title for a RouteNode.**
```php 
    'contact' => [
        'title' => 'Contact us!'
    ]
```
You can now call e.g. `route_tree()->getNode('contact')->getTitle()` to retrieve the string `Contact us!` from anywhere in your application.

**Example 2: Setting a title for a RouteNode per language.**
```php 
    'contact' => [
        'title' => [
            'en' => 'Contact us!',
            'de' => 'Kontaktieren Sie uns!'
        ]
    ]
```
You can now call e.g. `route_tree()->getNode('contact')->getTitle()` to retrieve the title for that page in the current locale.
You can also retrieve it in a specific language by stating the language as the second parameter of getTitle (e.g. `getTitle(null, 'en')`) will always return the english title, even if the current locale is 'de'.

**Example 3: Setting a title for a RouteNode using a closure.**
```php 
    'contact' => [
        'title' => function($parameters,$locale) {
            if ($locale === 'en') {            
                if ($parameters['team'] === 'support') {
                    return 'Contact our support-team!';
                }
                else if ($parameters['team'] === 'office') {
                    return 'Contact our office-team!';
                }
            }
            else if ($locale === 'de') {            
                 if ($parameters['team'] === 'support') {
                     return 'Kontaktieren Sie unser Support-Team!';
                 }
                 else if ($parameters['team'] === 'office') {
                     return 'Kontaktieren Sie unser Office-Team!';
                 }
             }
        }]
    ]
```

As you can see, the closure receives 2 parameters. The first one is an array of desired URL-parameters (using any currently active parameters as default), the second one is the desired locale (using the current locale as default).
You could now call e.g. `route_tree()->getNode('contact')->getTitle(['team' => 'support'],'en')` to retrieve the english title for the support-version of this page.

If `getTitle()` is called on a RouteNode, which does not have a title set, an auto-translation is tried (see below for how that works). If the title could not be auto-translated, the upper-cased node-name itself will be used (e.g. The title would then automatically be `Contact`).

###### **'navTitle'**:

In many applications you may want to use a different/shorter title in menus or breadcrumbs.

RouteTree provides the this functionality out of the box. Just see the description of the 'title' functionality above and change all references of `title` into `navTitle`.

###### ** Action specific (nav-)titles (e.g. 'title_show', 'title_edit', 'navTitle_show', 'navTitle_edit') **:

You can also define action-specific titles or navTitles by appending an underscore and the action name to 'title' or 'navTitle'.

The could now call e.g. `route_tree()->getNode('contact')->getAction('show'))` to retrieve the title for the `show`-action of the `contact`-node.

###### **'values'**:

[TODO]


### Auto-translation

Routetree also includes some magic regarding automatic translation. The basic concept is to map the hierarchy of the route-tree to a folder-structure within the localization-folder.

The config-key `localization.base_folder` sets the base-folder for the localization-files- and folders for route-tree. The default value is `pages`, which translates to the folder `\resources\lang\%locale%\pages`.

There are 2 seperate auto-translation-functionalities:
1. Auto-translation of a node's (meta-)data (e.g. like path-segment, title, abstract, custom data, etc.)
2. Auto-translation of regular page-content.

#### Auto-translation of page-meta-data

This provides an easy and intuitive way of configuring multi-language variants of the path-segment, page-title, or any other custom information for routes through laravel's localization-files.
 
Each route-node is represented as a folder, and within the folder for a node resides a file, that contains all auto-translation-information. How this file is named is configured under the config-key `localization.file_name`. The default value is `pages`, which means information on all 1st-level-pages should be placed in this file: `\resources\lang\%locale%\pages\pages.php`

**Example:**
Let's assume, you have defined the following route-tree-array (any actions or other options are missing for simplicity's sake):
```php
    'company' => [
        'children' => [
            'history' => [],
            'team' => [
                'children' => [
                    'office' => [],
                    'service' => []
                ]
            ]
        ]
    ],
    'contact' => []
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

Note that there is an additional entry in the title-array with an empty string as the key and "Home" as the value. This is title of the root-page.

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
    'abstract' => [
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
    'abstract' => [
        'office' => 'Hier finden Sie unsere Büro-Mitarbeiter.',
        'service' => 'Hier finden Sie unsere Service-Mitarbeiter.',
    ]
];
```

With this setup, the segments defined in the language-files will automatically be used for the route-paths of their corresponding nodes.
Also the title will be fetched with each getTitle-call submitted for a specific node (e.g. `route_tree()->getNode('company.team.service')->getTitle()` would return `Büro`, if the current locale is german. 
The same thing is possible with the abstract (or any other custom parameter). (e.g. `route_tree()->getNode('company.team.service')->getAbstract()` would return `Hier finden Sie unsere Service-Mitarbeiter.`, if the current locale is german.

You can also set action-specific titles or navTitles via auto-translation by appending an underscore and the action to the node-name. This is very useful for resource nodes. Here is an example:

```php
<?php
return [
    'title' => [
        'users' => 'Users',
        'users_create' => 'Create news user',
        'users_show' => 'User :userName',
        'users_edit' => 'Edit user :userName',
    ],
];
```

#### Auto-translation of regular page-content

In many cases you will also want to translate page-content in your views. The RouteTree includes a handy helper-function called `trans_by_route()`, that will try and look for a translation in the current node's content-language-file.

Using the example above the location of this file for the `office` page would be : `./company/team/office.php`

### Important RouteTree-methods

For the already mentioned and explained methods `root()`, `node()` please see the corresponding sections above.

Here are some other useful methods of the RouteTree-class:

* **route_tree**: Get the root-node which is also the whole route-tree.
* **getCurrentNode**: Get the currently active node.
* **getCurrentAction**: Get the currently active action.
* **doesNodeExist**: Checks, if a node within the route-tree. It takes one parameter, which is the node-id to be checked. (e.g.: `route_tree()->doesNodeExist('company.team.office')`)
* **getNode**: Get's and returns the RouteNode via it's Id. (e.g.: `route_tree()->getNode('company.team.office')`)

### Important RouteNode-methods

For the already mentioned and explained methods `getTitle`, `getValues` and `getData` please see the corresponding sections above.

Here are some other useful methods of the RouteNode-class:

* **getParentNode**: Gets the parent node of this node. (e.g.: `route_tree()->getNode('company.team.office')->getParentNode()` would retrieve the node with the ID `company.team`.)
* **getParentNodes**: Gets an array of all hierarchical parent-nodes of this node (with the root-node as the first element). (e.g.: `route_tree()->getCurrentNode()->getParentNodes()` would retrieve an array of all ancestral-nodes of the currently active node up to to the root-node. This is very useful for site-maps or breadcrumbs.)
* **hasChildNodes**: Checks, if this node has any child-nodes.
* **getChildNodes**: Get an array of all child-nodes (e.g. useful for sub-menus).
* **hasChildNodes**: Checks, if this node has any child-nodes.
* **getChildNode**: Checks, if this node has a child-node with the stated name.
* **getId**: Get the full Id of this node.
* **getUrlByAction**: Gets the url of a certain action of this node. It takes the following parameters:
     * string $action: The action name (e.g. index|show|get|post|update,etc.) (defaults='index').
     * array $parameters: An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * string $language: The language this url should be generated for (default=current locale).
* **isActive**: Checks, if the current node is currently active (optionally with the desired parameters) (e.g. useful to apply a CSS-class to an active link).
* **nodeOrChildIsActive**: Checks, if the current node or one of it's children is currently active (optionally with the desired parameters) (e.g. useful to apply a CSS-class to an active link).

### Important RouteAction-methods

* **getUrl**: Get the URL to this action. It takes the following parameters:
     * array $parameters: An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
     * string $language: The language this url should be generated for (default=current locale).

### Helper functions

Several helper-functions are included with this package:

* **route_tree**: Gets the RouteTree singleton from Laravel's service-container. It can be used anywhere in your application (controllers, views, etc.) to access the RouteTree service.

* **route_node_id**: Gets the node-id of the currently active RouteNode.

* **route_node_url**: Generate an URL to the action of a route-node. It takes the following parameters:
      * string $nodeId: The node-id for which this url is generated (default=current node.
      * string $action: The node-action for which this url is generated (defaults='index').
      * array $parameters: An associative array of [parameterName => parameterValue] pairs to be used for any route-parameters in the url (default=current route-parameters).
      * string $language: The language this url should be generated for (default=current locale).
      

* **trans_by_route**: Translates page-content using the current node's content-language-file (see section `Auto-translation of regular page-content` above).