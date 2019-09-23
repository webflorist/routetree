<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Start Paths with locale?
    |--------------------------------------------------------------------------
    |
    | Set to false, if you don't want paths starting with locale.
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

];
