# Lumen Mocker
Lumen mocker is a Mock Server which created base on [Lumen](https://lumen.laravel.com/docs/9.x) and 
[Prism](https://github.com/stoplightio/prism) for creating Mock Server for different services.
With this service you can have Mock server and Contract Testing.

# Introduction

## OPEN API SPECIFICATION
The [OpenAPI Specification (OAS)](https://www.openapis.org) defines a standard, language-agnostic interface to HTTP APIs which allows both humans 
and computers to discover and understand the capabilities of the service without access to source code, documentation, 
or through network traffic inspection. When properly defined, a consumer can understand and interact with the remote service 
with a minimal amount of implementation logic.

An OpenAPI definition can then be used by documentation generation tools to display the API, code generation tools to 
generate servers and clients in various programming languages, testing tools, and many other use cases.


# Configuration
In the config directory we have 2 configuration files:
* services
  * for list of the services or products which you want to have mock server, like user, shop, ...
  * If you want to de-activate a service just change the `active` value to false
* settings
   - general configuration like Base_directory, docker path,...
   - You can set the docker directory/ images and version of the docker file.

# Docker Generation Commands 

Generate all of the docker images for available services:
```bash
php artisan make:docker
```
Generate docker image file for a specific service
```bash
php artisan make:docker-image {SERVICE_NAME} {PORT}

// example,
 php artisan make:docker-image shop 8094
```

## License
The Lumen Mocker is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
