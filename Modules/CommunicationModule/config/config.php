<?php

return [
    'name' => 'CommunicationModule',
    'integrations' => [
        'zoom' => [
            'client_id' => env('COMM_ZOOM_CLIENT_ID', ''),
            'client_secret' => env('COMM_ZOOM_CLIENT_SECRET', ''),
            'redirect_uri' => env('COMM_ZOOM_REDIRECT_URI', ''),
            'authorize_url' => env('COMM_ZOOM_AUTHORIZE_URL', 'https://zoom.us/oauth/authorize'),
            'token_url' => env('COMM_ZOOM_TOKEN_URL', 'https://zoom.us/oauth/token'),
            'api_base_url' => env('COMM_ZOOM_API_BASE_URL', 'https://api.zoom.us/v2'),
        ],
        'google_classroom' => [
            'client_id' => env('COMM_GOOGLE_CLASSROOM_CLIENT_ID', ''),
            'client_secret' => env('COMM_GOOGLE_CLASSROOM_CLIENT_SECRET', ''),
            'redirect_uri' => env('COMM_GOOGLE_CLASSROOM_REDIRECT_URI', ''),
            'authorize_url' => env('COMM_GOOGLE_CLASSROOM_AUTHORIZE_URL', 'https://accounts.google.com/o/oauth2/v2/auth'),
            'token_url' => env('COMM_GOOGLE_CLASSROOM_TOKEN_URL', 'https://oauth2.googleapis.com/token'),
            'api_base_url' => env('COMM_GOOGLE_CLASSROOM_API_BASE_URL', 'https://classroom.googleapis.com/v1'),
            'scopes' => [
                'https://www.googleapis.com/auth/classroom.courses.readonly',
                'https://www.googleapis.com/auth/classroom.coursework.students',
                'openid',
                'email',
                'profile',
            ],
        ],
    ],
    'webhooks' => [
        'zoom' => [
            'secret' => env('COMM_WEBHOOK_ZOOM_SECRET', ''),
        ],
        'google_classroom' => [
            'secret' => env('COMM_WEBHOOK_GOOGLE_CLASSROOM_SECRET', ''),
        ],
    ],
];
