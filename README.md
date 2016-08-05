# nicat/routetree
Advanded route management for Laravel 5

## Description
This package includes a special API for creating Laravel-routes, that intends to solve some of the shortcomings of the standard routes-generation of Laravel (especially regarding multi-language sites). It's main features are:

* Create a hierarchical multi-language route-tree from a simple array or any other custom implementation.
* The package then creates all desired routes in all configured languages using the language as the first URL-segment (e.g. `en/company/team/contact`).
* Translated routes are also a central feature of this package. It allows for language-specific URLS (e.g.  `en/company/team/contact` for english and `de/firma/team/kontakt` for german).
* This route-tree can then be used to easily create:
  * Language-switching menus.
  * Breadcrumb-menus.
  * Sitemap-menus.
  * Non-language specific links to a specific node/page (e.g. using the included helper-function `route_node_url($nodeID)` you can link to a specific page in the current language in your view (e.g. `route_node_url('company.team.support')` will create a link to the page behind this node-ID in the currently set locale.
* You can use standard-Laravel-localization-files to configure localized path-segments, page-titles and any other custom page-info (e.g. abstract, etc.).

## Installation
1. Require the package via composer:  
```php 
composer require nicat/routetree
```
2. Add the Service-Provider to config/app.php:
```php 
Nicat\RouteTree\RouteTreeServiceProvider::class
```
3. Define all locales you want to ust on your website under the key `locales` inside your `app.php` config file. E.g.:
```
'locales' => ['de' => 'Deutsch', 'en' => 'English'],
```
4. Publish config (optional):
```
php artisan vendor:publish --provider="Nicat\RouteTree\RouteTreeServiceProvider"
```

## Terminology
To understand the usage of this package, you should know the meaning of the following terms:

* **RouteTree:** The name of this package and also the hierarchical tree of RouteNodes of all pages of a website. It's top-most node ist the RootNode.
* **RouteNode:** A RouteNode is a single node in the RouteTree. Each node (except the RootNode) has one parent and can have one or more child-nodes. In most cases a node is also a page and results in a path-segment of the URL. Middlewares configured for a node are normally inherited to it's children. A RouteNode has the following important properties:
  * **Name:** The name of the RouteNode is a single term describing this node. It is used as part of the ID and consequently for the name of the generated Laravel-route. It is also the default value for this node's path-segment and page-title.
  * **ID:** The ID of a RouteNode is the names of itself and all it's ancestors up to the RootNode (from right to left), separated by dots. (e.g. The RouteNode with the name 'support' and the ancestor-nodes being 'team' and 'company' has the ID 'company.team.support'.). An ID is always unique, no tow RouteNodes can have the same ID.
* **RouteAction:** A RouteNode can (but must not have) one or more actions. Each action results in the generation of one Laravel-route per language. Each such route has a name using the following syntax: %language%.%RouteNodeId%.%action% (e.g. en.company.team.support.index). The available actions are the ones used by laravel itself with it's [resourceful resource controllers](https://laravel.com/docs/master/controllers#restful-resource-controllers).

## Usage

### Accessing the RouteTree-service
The best way to interact with the RouteTree-service is by using the helper-function `route_tree()`, which returns the RouteTree-singleton from Laravel's service container.

Of course you can also retrieve the RouteTree-singleton directly from the service container using `app('Nicat\RouteTree\RouteTree')` or `app()['Nicat\RouteTree\RouteTree']`.

### Defining the RouteTree

#### Defining the RouteTree via arrays 

Your can define RouteNodes in your `routes.php` by handing a specially composed array-structure over to the appropriate route-tree-method. Each RouteNode is defined via an array, that can have several keys (=options): 

##### Possible RouteNode-options
Here is a list of possible RouteNode-options usable in the array to define the RouteTree. Each option is a key in the array of a RouteNode and should have the described usage. All options are optional.

###### **'children'**:

Any RouteNode can have other RouteNodes as children, which are to be defined in an array residing under the key `children`. 

This is mainly useful to generate a hierarchical RouteTree, in which middleware and namespaces are inherited to the children of RouteNodes, and that hierarchy is also reflected in the URL-paths.

Example:
```php 
    'contact' => [
        /*... options of the RouteNode`'contact' ...*/      
        'children' => [
            'support' => [
                /*... options of the RouteNode`'support' ...*/   
            ],
            'office' => [
                /*... options of the RouteNode`'office' ...*/   
            ],
        ]
    ]
```

Since the root-line of every RouteNode is reflected in it's ID, this example would create 3 RouteNodes with the following IDs: `contact`, `contact.support`, `contact.office`.

Any options like middleware or namespace set for the `contact`-node as well as it's URL-path will be inherited to it's children (more on this at the discriptions of all possible options below.

###### **'segment'**:

The path-segment to be used for creating urls to this node, which is then also a part of the URL to any child-nodes.

The values can be either a string, which will be used as a path-segment for all languages, or an array of language => segment pairs defining different segments for each language.

**Example 1: Defining a static path-segment to be used for all languages.**
```php 
    'contact' => [
        'segment' => 'contact_us'
    ]
```
The URI to this node will be e.g. `/en/contact_us` for the english version and `/de/contact_us` for the german version.

**Example 2: Defining seperate segments for each language.**
```php 
    'contact' => [
        'segment' => [
            'en' => 'contact',
            'de' => 'kontakt'
        ]
    ]
```
The URI to this node would be e.g. `/en/contact` for the english version and `/de/kontakt` for the german version.

If this option is omitted, an auto-translation is tried (see below for how that works). If the segment could not be auto-translated, the node-name itself will be used (e.g. The URIs would be `/en/contact` for english and `/de/contact` for german).

###### **Actions**:

The actions to be attached to this RouteNode. Each action is to be defined using it's name as an array key.  Here is a list of available actions, the method used to create their routes, and their resulting route names:
                                                                                                            
Action | Method | Route Name
-------|--------|-----------
index|get|%language%.%RouteNodeId%.index
create|get|%language%.%RouteNodeId%.create
store|post|%language%.%RouteNodeId%.store
show|get|%language%.%RouteNodeId%.show
edit|get|%language%.%RouteNodeId%.edit
update|put|%language%.%RouteNodeId%.update
destroy|delete|%language%.%RouteNodeId%.destroy
get|get|%language%.%RouteNodeId%
post|post|%language%.%RouteNodeId%

Of course, you can not register two actions, that use the same HTTP-method (e.g. 'index' and 'show' both use the GET-method and can not be used together with the same node).

The value of an action-definition is always a sub-array, whose structure depends on desired type of action. The type can either be a closure or a controller-method (just as with normal Laravel-routes-definition), but additionally you define a simple redirect or view, that should be displayed in an easily configurable and readable way. 

**Example 1: Defining an index-action using a closure**
```php 
    'contact' => [
        'index' => ['closure' => function () {
            return 'welcome';
        }]
    ]
```
With this definition, `welcome` would be displayed with a GET-request to `/en/contact` (or any other defined language).

**Example 2: Defining an store-action using a controller-method**
```php 
    'index' => [
        'store' => ['uses' => 'ContactController@index'],
    ]
```
With this definition, the `index` method of the `ContactController` would be called on a GET-request to `/en/contact`.

**Example 3: Defining an index-action using a redirect**
```php 
    'contact' => [
        'index' => ['redirect' => 'contact.support'],
        'children' => [
            'support' => [
                'index' => ['uses' => 'SupportController@index'],
            ]
        ]
    ]
```
With this definition, a GET-request to `/en/contact` would be redirected to `/en/contact/support` with then would call the `index` method of the `SupportController`.

**Example 4: Defining an index-action using a view**
```php 
    'contact' => [
        'index' => ['view' => 'contact'],
    ]
```
With this definition, the view `contact` would be rendered on a GET-request to `/en/contact`.

If you do not define any action at all on a node, no Laravel-routes will be generated at all for this node, but it will still be visible in the RouteNode-IDs (and this the route-names) as well as the paths of it's children. It's defined middlewares will also be inherited. So this type of usage comes very close to Laravel's own [Route Groups](https://laravel.com/docs/master/routing#route-groups). 

###### **'middleware'**:

The middlewares that should be attached to all generated routes for this node. These middelwares will be automatically inherited to the RouteNode's children, if it is not specifically disabled by setting the sub-key 'inherit' to false. You can also specify the parameters to be handed over to the middleware.

**Example:**
```php 
    'contact' => [
        'index' => ['uses' => 'ContactController@index'],
        'middleware' => [
            'auth' => ['inherit' => false,
            'role' => ['parameters' => ['editor','admin']]
        ],
        'children' => [
            'support' => [
                'index' => ['uses' => 'SupportController@index'],
                'middleware' => [
                    'throttle' => []
                ]
            ]
        ]
    ]
```
In this example the `index` action of the `contact`-node will have 2 middlewares attached: `auth` with no parameters and `role` with the parameters `editor,admin`.
It's child node `support` will inherit the `role` middleware from it's parent, but not the `auth` middleware, because it has `inherit` set to  `false`. Additionally it will have the middleware `throttle` attached to it`s route.

###### **'namespace'**:

The namespace, that should be prepended to all controller-definitions of all actions of a RouteNode. The default-value is `App\Http\Controllers`. This namespace is inherited to all children (unless they define a namespace themselves).

**Example:**
```php 
    'contact' => [
        'index' => ['uses' => 'ContactController@index'],
        'namespace' => `My\Namespace`,
        'children' => [
            'support' => [
                'index' => ['uses' => 'SupportController@index'],
            ],
            'office' => [
                'index' => ['uses' => 'OfficeController@index'],
                'namespace' => `My\Other\Namespace`,
            ]
        ]
    ]
```
In this example the `index` action of the `contact`-node will call `My\Namespace\ContactController@index`. The  `index` action of the `support`-node will call `My\Namespace\SupportController@index`, because it is inherited from it's parent. The  `index` action of the `office`-node however has it's own namespace defined, and will thus call `My\Other\Namespace\OfficeController@index`.
It's child node `support` will inherit the `role` middleware from it's parent, but not the `auth` middleware, because it has `inherit` set to  `false`. Additionally it will have the middleware `throttle` attached to it`s route.

This is especially useful, if certain areas of your website are using controllers coming from a vendor-package.

If you have all your controllers under `App\Http\Controllers` you will not need this option. And if you use sub-folders and -namespaces to structure your controllers within `App\Http\Controllers` it's better to use the `appendNamespace` option described next.
 
###### **'appendNamespace'**:

The sub-namespace, that should be appended the inherited namespace.

**Example:**
```php 
    'contact' => [
        'index' => ['uses' => 'ContactController@index'],
        'appendNamespace' => `Contact`,
        'children' => [
            'support' => [
                'index' => ['uses' => 'SupportController@index'],
                'appendNamespace' => `Support`,
            ]
        ]
    ]
```
In this example the `index` action of the `contact`-node will call `App\Http\Controllers\Contact\ContactController@index`. The  `index` action of the `support`-node will call `App\Http\Controllers\Contact\Support\SupportController@index`.

###### **'inheritPath'**:

Per default all nodes inherit their paths to their children. So if a RouteNode `contact` having the english URI `/en/contact` has a child called `support`, that child's english URI will be `en/contact/support`.

If you do not want this behaviour, e.g. because you are using the parent node only to set middleware and namespace or for hierarchical reasons, you can achieve this as described in the following example: 

```php 
    'contact' => [
        'index' => ['uses' => 'ContactController@index'],
        'inheritPath' => false,
        'children' => [
            'support' => [
                'index' => ['uses' => 'SupportController@index'],
            ]
        ]
    ]
```
In this example the english URI to the `index` action of the `contact`-node will be `/en/contact`. Tthe URI to it's child `support` will be `/en/support` (instead of `/en/contact/support`, when setting `inheritPath` to true or omitting it.

Please note, that the ID of the `support`-node will still be `contact.support`, and any middleware or namespace defined with `contact` will still be inherited to it's `support`-child.

###### **'resource'**:

This option provides an easy way to register resourceful routes (much like the Laravel-[Route::resource() method](https://laravel.com/docs/master/controllers#restful-resource-controllers).

Let's see this example:
```php 
    'photos' => [
        'resource' => [
            'name' => 'photo',
            'controller' => 'ArticleController'
        ]
    ]
```

This will generate a full set of resourceful routes (just as `Route::resource('photo', 'PhotoController')` would) for the RouteNode `photos`:

 English Path              | Action / Controller Method
---------------------------|--------------
 `/en/photos`              | index
 `/en/photos/create`       | create
 `/en/photos`              | store
 `/en/photos/{photo}`      | show
 `/en/photos/{photo}/edit` | edit
 `/en/photos/{photo}`      | update
 `/en/photos/{photo}`      | destroy
 
You can also define an array of actions under the keys `only` or `except` to include or exclude certain actions (again similar ro Laravel's `Route::resource` method. Example:
```php 
    'photos' => [
        'resource' => [
            'name' => 'photo',
            'controller' => 'ArticleController',
            'only' => ['index','show']
        ]
    ]
```
This example would only create routes for the actions `index` and `show`.

Note, that a resource-node can also have children and apply any other functionality as described above.

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
You can also retrieve it in a specific language by stating the language as the second parameter of getTitle (e.g. `getAbstract(null, 'en')`) will always return the english title, even if the current locale is 'de'.

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

###### **'values'**:

[TODO]

###### **Custom parameters**:

You can also set any information your want with a RouteNode, using an array-key, that is not used for any of the fixed options described above.

This information can then be retrieved by calling the `getData()`-method of a RouteNode-object, or by calling a corresponding magic-getter-method (e.g. `getData('abstract')` and `getAbstract()` would both retrieve any information set in the route-generation-array under the key `abstract`.

Furthermore custom parameters are handled the same way as the `title` parameter above. This means you can set this information statically for all languages or per language using a language-array, or even set a closure. Just look at the examples above for setting a `title` and use any desired keyword instead. And as with the `title` usage, you can also handle custom parameters using auto-translation (as described below). 
 
This is very useful to e.g.:

* Set the class of an icon, that should be visible in the menu next to the page-title.
* Set a language-specific abstract for each page, displayed on a sitemap.
* and much much more...

##### Defining a root-node

Each site has a single root-node, which also is the top-most RouteNode in the RouteTree. All other RouteNodes are descendants of that root-node.

A root-node is defined calling the `setRootNode()` method on the RouteTree-service. It's only parameter is an array including the options of the root-node (available options are described above.

**Example:**
```php 
    route_tree()->setRootNode([
        'index' => ['view' => 'welcome']
    ]);
```

If you put this in your `routes.php` file, the root-page on your website will display the content of the `welcome`-view, and will be callable for all configured languages. E.g. www.yoursite.com/en or www.yoursite.com/de will lead to the language-specific version of the startpage. Calling www.yoursite.com will be redirected to configured default-locale-version.  

You can also include any `children` or even hand over the whole RouteTree-array to the `setRootNode()` method.

##### Adding a single node

If you want to add a single node to an already defined parent node, you can use the `setNode()` method. It takes three parameters:
* $nodeName: a string, e.g. `support`
* $nodeData: an array of the desired RootNode-options (again optionally also with `children`)
* $parentNodeId: a string with the ID of the parent-node (e.g. `company.team`). If this is omitted, the root-node will be used as a parent for this domain.

**Example:**
```php 
    route_tree()->addNode(
        'support',
        [
            'index' => ['view' => 'support']
        ],
        'company.team'
    );
```

##### Adding multiple nodes

You can also add multiple (same-level) nodes at once using the `setNodes()` method. It takes two parameters:
* $nodes: a multi-dimensional array, whose keys are the node-name and whose values are the node-options.
* $parentNodeId: a string with the ID of the parent-node (e.g. `company.team`). If this is omitted, the root-node will be used as a parent for this domain.

**Example:**
```php 
    route_tree()->addNodes(
        [
            'support' => [
                'index' => ['view' => 'support']
            ],
            'office' => [
                'index' => ['view' => 'office']
            ]
        ],
        'company.team'
    );
```

### Auto-translation

Auto-translation is used with several functions of routetree and provides an easy and intuitive way of configuring multi-language variants of the path-segment, page-title, or any other custom information for routes through laravel's localization-files.
 
The basic concept is to map the hierarchy of the route-tree to a folder-structure within the localization-folder. Each route-node is represented as a folder, and within the folder for a node resides a file, that contains all auto-translation-information. 

There are 2 relevant config-items in the `routetree.php`-config-file published by this package:
* **localization.baseFolder**: This is the base-folder for the localization-files- and folders for route-tree. The default value is `pages`, which translates to the folder `\resources\lang\%locale%\pages`.
* **localization.fileName**: This is the name of the files, route-tree should use for it's auto-translation functionality. The default value is `pages`, which means information on all 1st-level-pages should be placed in this file: `\resources\lang\%locale%\pages\pages.php`.

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

### Important RouteTree-methods

For the already mentioned and explained methods `setRootNode`, `addNode` and `addNodes` please see the corresponding sections above.

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