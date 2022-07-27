# laravel-test-generator
Laravel package for generating api test automatically.

## Installation

```bash
$ composer require lain/laravel-test-generator
```

## Configuration

Copy the package config to your local config with the publish command:

```bash
$ php artisan vendor:publish --tag=test-generator
```

This will add a new config to ``config/test-generator.php``:

```php
return [
    'get_token' => 'app(Helper::class)->getAccessTokenForEmailToOrg()',
];
```

## Usage
Create ``test-swagger.json`` in your project root directory, It contains the api tests that need to be created, Please make sure it conforms to OpenAPI format (swaager 3.0).

To do so, simply run ```php artisan laravel-test:generate TEST_FILE_NAME``` in your project root. This will write all the test cases into the file based on test-swagger json.
