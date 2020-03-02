<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App locales
    |--------------------------------------------------------------------------
    |
    | Set all locales to use in this app (e.g. ['en','de']).
    |
    | Set to null for a single-language app (using config 'app.locale').
    | This will result in paths not starting with locale (e.g. /en/).
    |
    */
    'locales' => [],

    /*
    |--------------------------------------------------------------------------
    | Create absolute paths instead of relative paths by default?
    |--------------------------------------------------------------------------
    |
    | Can still be overridden using the 'absolute()' method
    | on route-generation (e.g. route_node_url()->absolute()).
    |
    */
    'absolute_urls' => true,

    /*
    |--------------------------------------------------------------------------
    | Fallback node
    |--------------------------------------------------------------------------
    |
    | This fallback node will be used by RouteTree if:
    | - the current node was requested but could not be determined
    |   (e.g. in case of a 404),
    | - a specific node-ID was requested but does not exist
    |   (e.g. after deletion or moving a node).
    |
    | Setting this to null will throw an exception, if a node could not be found,
    | which is the recommended value for development or testing environments,
    | since it catches errors (e.g. a mis-typed RouteNode-ID).
    |
    | In production environments, the default-value sets this to the
    | root-node-ID (= empty string = '') to circumvent NodeNotFoundExceptions.
    | You can also set any other fallback node (e.g. a dedicated '404' node).
    |
    */
    'fallback_node' => env('APP_ENV') === 'production' ? '' : null,

    /*
    |--------------------------------------------------------------------------
    | Translation Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for the auto-translation
    | functionality of the RouteTree package.
    |
    */
    'localization' => [

        /*
         * The base-folder for translations (optionally including any namespace).
         */
        'base_folder' => 'pages',

        /*
         * The name of the file, in which auto-translations reside.
         */
        'file_name' => 'pages',

        /*
         * Automatically translate resource path-suffixes
         * (/create and /edit).
         */
        'translate_resource_suffixes' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for generation
    | of a sitemap XML file via the artisan command 'routetree:generate-sitemap'.
    |
    */
    'sitemap' => [

        /*
         * Name of the output file (relative to laravel root).
         */
        'output_file' => 'public/sitemap.xml',

        /*
         * Base URL of generated urls.
         * e.g. "http://localhost".
         */
        'base_url' => config('app.url')

    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure settings for the integrated REST-API.
    |
    */
    'api' => [

        /*
         * Should API routes be registered?
         */
        'enabled' => false,

        /*
         * Base path of the API routes.
         */
        'base_path' => 'api/routetree/'

    ],

];
