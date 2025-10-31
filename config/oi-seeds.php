<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | The base storage path where exported seeder JSON files will be saved.
    | This path is relative to the storage_path() directory.
    |
    */

    'storage_path' => env('OI_SEEDS_STORAGE_PATH', 'app/private/seeders'),

    /*
    |--------------------------------------------------------------------------
    | Default Unique By Column
    |--------------------------------------------------------------------------
    |
    | The default column to use for upsert operations when not specified
    | in the seeder class.
    |
    */

    'default_unique_by' => env('OI_SEEDS_DEFAULT_UNIQUE_BY', 'id'),

    /*
    |--------------------------------------------------------------------------
    | Auto-Discover Seeders
    |--------------------------------------------------------------------------
    |
    | Whether to automatically discover exportable seeders in the
    | database/seeders directory when running export/import commands.
    |
    */

    'auto_discover' => env('OI_SEEDS_AUTO_DISCOVER', true),

    /*
    |--------------------------------------------------------------------------
    | JSON Options
    |--------------------------------------------------------------------------
    |
    | JSON encoding options for exported files. By default, we use
    | JSON_PRETTY_PRINT for readability and JSON_UNESCAPED_UNICODE
    | to preserve special characters.
    |
    */

    'json_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,

];
