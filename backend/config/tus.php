<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Redis configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define the redis configuration for the tus server.
    |
    */
    "redis" => [
        "host" => env("REDIS_HOST", "redis"),
        "port" => env("REDIS_PORT", "6379"),
        "database" => env("REDIS_DB", 0),
    ],
];
