<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Fox Mockery
Fox Mockery is a Mock Server which created base on [Laravel](https://lumen.laravel.com/docs/9.x).
With this service you can have Mock server for your contract testing.

# Configuration
In the config directory we have 2 configuration files:
* services
  * for list of the services or products which you want to have mock server, like user, shop, ...
  * If you want to de-activate a service just change the `active` value to false
* settings: general configuration like Base_directory, docker path,... 

# Generation Commands 
In this section you can use commands to generate files.

## Schema

Generate Schema according to the json files in the mock service directory
```bash
php artisan make:schema  
php artisan make:schema -f

php artisan make:schema {SERVICE_NAME}

// Example
 php artisan make:schema user
 
 // OUTPUT
 AFTER RUNNING THIS COMMAND index.json FILE WILL CREATE IN THE SERVICE DIRECTORY!'
```

## FILE CONVERTER COMMAND
if you want to conver a file from json to yml or yml to json you can use below commands:
```bash
php artisan convert:files {FROM} {TO}

// YML to JSON
php artisan convert:files test.yml  test.json

// JSON to YML
php artisan convert:files test.json  test.yml
```

# Next feature updates 
- Support OPEN API SPECIFICATION V3.0 and V3.1
- Generating docker images per service
- Generate beautiful UI for the index page

## OPEN API SPECIFICATION
The [OpenAPI Specification (OAS)](https://www.openapis.org) defines a standard, language-agnostic interface to HTTP APIs which allows both humans
and computers to discover and understand the capabilities of the service without access to source code, documentation,
or through network traffic inspection. When properly defined, a consumer can understand and interact with the remote service
with a minimal amount of implementation logic.

An OpenAPI definition can then be used by documentation generation tools to display the API, code generation tools to
generate servers and clients in various programming languages, testing tools, and many other use cases.

## License
The Lumen Mocker is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

