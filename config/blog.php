<?php

return [

    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    'media_disk' => env('MEDIA_DISK', env('COVER_IMAGE_DISK', 'public')),

    'cover_disk' => env('MEDIA_DISK', env('COVER_IMAGE_DISK', 'public')),

];
