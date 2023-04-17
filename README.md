<p style="text-align: center">
<img src="https://isportz.co/wp-content/uploads/2021/01/isportz-logo.png" alt="iSportz" width="50%">
<img src="https://dev-auth.isportz.co/resources/nq4du/admin/keycloak.v2/logo.svg" alt="iSportz" width="50%">
</p>

# Platformization Keycloak REST API Service

Platformization Keycloak is a Laravel package that gives you access to keycloak's admin REST APIs.

For API references visit [Keycloak Admin REST API](https://www.keycloak.org/docs-api/15.0/rest-api/index.html), To understand Keycloak Administration refer [Server Administration Guide](https://www.keycloak.org/docs/latest/server_admin/index.html).

## Getting Started

### Installation

Faker requires PHP >= 8.0.

Download this package and put this inside your laravel project's root directory `fm-subscription-back\packages\keycloakapiservices`.

Open the `composer.json` file and add the below code before `"require": {}` object.

```shell
"repositories": [
    {
      "type": "path",
      "url": "packages/keycloakapiservices"
    }
],
```
Add package name `packages/keycloakapiservices": "*"` inside `"require": {}` object like below and save the file.
```shell
    "require": {
        "php": "^8.0",
        "laravel/framework": "^9.0",
        "packages/keycloakapiservices": "*"
    },
```

Run the `composer update` to complete installation.

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
                ->group(base_path('vendor/packages/keycloakapiservices/src/routes/api.php'));
        });
    }
```


### Documentation

### Basic Usage

Refer the Postman collection `Platform KeyCloak API.postman_collection` which you can find inside this package folder.

## License

Platformization Keycloak REST API Service is owned by iSportz . Reach out [`LICENSE`](https://isportz.co/contact-us/) for more details.
