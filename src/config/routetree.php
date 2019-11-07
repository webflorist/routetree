<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App locales
    |--------------------------------------------------------------------------
    |
    | Set all locales to use in this app.
    | e.g. ['en','de']
    |
    */
    'locales' => [],

    /*
    |--------------------------------------------------------------------------
    | Start Paths with locale?
    |--------------------------------------------------------------------------
    |
    | Set to false, if you don't want paths starting with locale.
    | This only works, if only one locale is defined above
    |
    */
    'start_paths_with_locale' => true,

    /*
    |--------------------------------------------------------------------------
    | Create absolute paths instead of relative paths by default?
    |--------------------------------------------------------------------------
    |
    | Can still be overridden with function-parameters.
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
         * The base-folder for translations (optionally including any namespace)
         */
        'base_folder'  => 'pages',

        /*
         * The name of the file, in which auto-translations reside.
         */
        'file_name' => 'pages',

    ],


    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure settings for DB-supported routes.
    |
    */
    'database' => [

        /*
         * Enable DB-support (publishes migrations).
         */
        'enabled' => false,

    ],

];
