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
