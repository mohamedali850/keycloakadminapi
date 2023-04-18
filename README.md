<p style="text-align: center">
<img src="https://isportz.co/wp-content/uploads/2021/01/isportz-logo.png" alt="iSportz" width="50%">
<img src="https://dev-auth.isportz.co/resources/nq4du/admin/keycloak.v2/logo.svg" alt="iSportz" width="50%">
</p>

# Platformization Keycloak REST API Service

Platformization Keycloak is a Laravel package that gives you access to keycloak's admin REST APIs.

For API references visit [Keycloak Admin REST API](https://www.keycloak.org/docs-api/15.0/rest-api/index.html), To understand Keycloak Administration refer [Server Administration Guide](https://www.keycloak.org/docs/latest/server_admin/index.html).

## Getting Started

### Installation

Keycloak admin API requires 

PHP >= 8.0

Laravel >= 9.0

Guzzlehttp >= 7.2

Keycloakapiservices package require below dependency packages.
```shell
    "require": {
        "php": "^8.0",
        "laravel/framework": "^9.0",
        "guzzlehttp/guzzle": "^7.2"
    },
```
Run the below command to install the package.
```
composer require jinna/keycloakapiservices
```

Publish the keycloakapi config file using `php artisan vendor:publish --provider="KeycloakApiServices\KeycloakApiServiceProvider" --tag="config"`, A config file `keycloakapiservices.php` will be created in `config` folder

Add `KeycloakApiServices\KeycloakApiServiceProvider::class` in `config/app.php` file, at the end of `'providers' => []` array like below.

```php
'providers' => [
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        KeycloakApiServices\KeycloakApiServiceProvider::class
    ],
```

Add the below code in `app/Providers/RouteServiceProvider.php` file inside `boot()` method's `$this->routes(function () {})` group like below.
```php
    public function boot()
    {
        $this->configureRateLimiting();            
        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('vendor/jinna/keycloakapiservices/src/routes/api.php'));
        });
    }
```

Add/config the below variables in your `.env` file with appropriate values. 
```
//Keycloak Server Url
KEYCLOAK_BASE_URL="https://keycloakauth.yourdomain.com"
KEYCLOAK_REALM_ENDPOINT=/admin/realms
KEYCLOAK_CLIENTS_ENDPOINT=/clients
KEYCLOAK_ROLES_ENDPOINT=/roles
KEYCLOAK_ADMIN_CLIENT_ID=admin-cli
KEYCLOAK_ADMIN_USERNAME=keycloakadminusername
KEYCLOAK_ADMIN_PASSWORD="keycloakadminpassword"
```

### Documentation

### Basic Usage

Refer the Postman collection `Platform KeyCloak API.postman_collection` which you can find inside this package folder.

## License

Platformization Keycloak REST API Service is owned by iSportz . Reach out [`LICENSE`](https://isportz.co/contact-us/) for more details.
