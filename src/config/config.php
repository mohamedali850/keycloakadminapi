<?php

return [
    'realm' => [
        'name' => env('KEYCLOAK_API_REALM_NAME', null),
        'verify' => env('KEYCLOAK_API_APP_VERIFY', true),
        'clientId' => env('KEYCLOAK_CLIENT_ID', null)
    ]
];
