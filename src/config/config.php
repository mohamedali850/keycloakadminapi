<?php

return [
    'realm' => [
        'name' => env('KEYCLOAK_API_REALM_NAME', null), //Dynamic realm name to be get and set from request
        'verify' => env('KEYCLOAK_API_APP_VERIFY', false), //SSL certificate verification behavior of a request
        'clientId' => env('KEYCLOAK_CLIENT_ID', null) //Dynamic client Id to be get and set from request
    ]
];
