# laravel-test-generator
Laravel package for generating api test automatically.

[![tests](https://github.com/xuliangTang/laravel-test-generator/workflows/tests/badge.svg?branch=main)](https://github.com/xuliangTang/laravel-test-generator/actions?query=workflow%3Atests+branch%3Amain)
[![PHP Version Require](https://img.shields.io/packagist/php-v/lain/laravel-test-generator)](https://packagist.org/packages/lain/laravel-test-generator)
[![Latest Stable Version](https://img.shields.io/github/v/release/xuliangTang/laravel-test-generator?style=flat)](https://packagist.org/packages/lain/laravel-test-generator)

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
Create [``test-swagger.json``](https://github.com/xuliangTang/laravel-test-generator/blob/main/tests/test-swagger.json) in your project root directory, It contains the api tests that need to be created, Please make sure it conforms to OpenAPI format (swaager 3.0).

To do so, simply run ```php artisan laravel-test:generate TEST_FILE_NAME``` in your project root. This will write all the test cases into the file based on test-swagger json.
