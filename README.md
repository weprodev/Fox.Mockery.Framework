![Fox Mockery](https://github.com/Mekaeil/Fox.Mockery/blob/master/public/logo-150.png?raw=true)

# Fox Mockery

Fox Mockery is a Mock Server which created base on [Laravel](https://lumen.laravel.com/docs/9.x).

**You can use the Fox Mockery for:**

- Contract Testing
- API Documentation
- E2E Testing
- Generate Open API Specification

# Install on your local

follow below steps to run a Mock Server on your local.

1. Clone the repository
2. Run this command on the Fox Mockery directory `make install`
   > If the make command is not working for you run these commands
   1. `cp .env.example .env`
   2. `composer install`
   3. `php artisan serve`
   

3. Now you can [open it on your browser](http://localhost:8000) and then use [Insomnia](https://insomnia.rest/)
   or [Postman](https://www.postman.com/downloads/)

# Install and pull the API documentation
1. download [Insomnia Tool](https://insomnia.rest/download)
2. Clone this repository 
3. Now, you can test all the Petstores endpoints after running the docker on your local

![alt text](https://github.com/weprodev/Fox.Mockery/blob/master/public/petstore-open-api-specification-Fox-Mockery.png)

# Configuration

Fox Mockery has three config files:

- **fox_services**: you can define your services here
- **fox_openapis**: Open API Specification configuration
- **fox_settings**: main config of the Fox Mockery

> You can define different products or services as a service in
> `fox_services` config file, each service has a unique address.

# OPEN API SPECIFICATION

Open API Specification files will generate according to the json files in the mock service directory.

```bash
// generate Open API Specification for all service
make openapi   OR   php artisan fox:openapi

// generate Open API Specification for a specific service
make openapi {SERVICE_NAME}   OR   php artisan fox:openapi {SERVICE_NAME}

// Example 
make openapi petstore   OR   php artisan fox:openapi petstore
 
 // OUTPUT
 AFTER RUNNING THIS COMMAND index.oas.json, index.oas.yml and route.json FILES WILL CREATE IN THE SERVICE DIRECTORY!'
```

# Next features (TO DO)

- Support OPEN API SPECIFICATION V3.1
- Generating docker images per service
- Generate beautiful UI for the index page

## OPEN API SPECIFICATION

The [OpenAPI Specification (OAS)](https://www.openapis.org) defines a standard, language-agnostic interface to HTTP APIs
which allows both humans and computers to discover and understand the capabilities of the service without access to
source code, documentation, or through network traffic inspection. When properly defined, a consumer can understand and
interact with the remote service with a minimal amount of implementation logic.

An OpenAPI definition can then be used by documentation generation tools to display the API, code generation tools to
generate servers and clients in various programming languages, testing tools, and many other use cases.


## Path Structure Example
if you create a path json file, you can use below examples to add the contents:
```
{
  "/example/path": {
    "get": {
      "summary": "",
      "operationId": "",
      "tags": [
        ""
      ],
      "parameters": [
        {
          "name": "",
          "in": "", // "query", "header", "path" or "cookie"
          "description": "",
          "required": false,
          "schema": {
            "type": ""
          }
        }
      ],
      "responses": {
        "200": {
          "description": "",
          "headers": {
            "x-next": {
              "description": "",
              "schema": {
                "type": "string"
              }
            }
          },
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/..."
              },
              "example": [
                {}
              ]
            }
          }
        },
        "default": {
          "description": "unexpected error",
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/..."
              },
              "example": {}
            }
          }
        }
      }
    },
    "post": {
      "summary": "",
      "operationId": "",
      "tags": [
        ""
      ],
      "requestBody": {
        "description": "",
        "required": true,
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/..."
            }
          }
        }
      },
      "responses": {
        "200": {
          "description": "",
          "content": {
            "application/json": {
              "examples": [
                {
                  "id": 1,
                  "name": "",
                  "tag": ""
                }
              ]
            }
          }
        },
        "201": {
          "description": "Null response"
        },
        "default": {
          "description": "unexpected error",
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/Error"
              }
            }
          }
        }
      }
    }
  }
}
```

## License

The Fox Mockery is an open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

